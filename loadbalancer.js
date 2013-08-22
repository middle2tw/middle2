var http = require('http');
var https = require('https');
var Scribe = require('scribe').Scribe;
var Memcache = require('memcache');
var mysql = require('mysql');
var SSH2 = require('ssh2');
var fs = require('fs');

var loadConfig = function(){
    var content = fs.readFileSync('/srv/config/config.php');
    var regex = /putenv\(\'([^=]*)=([^\']*)\'\);/g;

    var match;
    var ret = {};
    while (match = regex.exec(content)) {
        ret[match[1]] = match[2];
    }
    return ret;
};

var config = loadConfig();

if (!config.MEMCACHE_PRIVATE_PORT) {
    throw "need MEMCACHE_PRIVATE_PORT";
}
var memcache = new Memcache.Client(config.MEMCACHE_PRIVATE_PORT, config.MEMCACHE_PRIVATE_HOST);
memcache.connect();

if (!config.MYSQL_HOST) {
    throw "need MYSQL_HOST";
}
var mysql_connection = mysql.createConnection({
      host     : config.MYSQL_HOST,
      user     : config.MYSQL_USER,
      password : config.MYSQL_PASS,
      database : config.MYSQL_DATABASE,
});

if (!config.MAINPAGE_HOST) {
    throw "need MAINPAGE_HOST";
}
var main_page_host = config.MAINPAGE_HOST;
var main_page_port = config.MAINPAGE_PORT;

if (!config.MAINPAGE_DOMAIN) {
    throw "need MAINPAGE_DOMAIN";
}
if (!config.APP_SUFFIX) {
    throw "need APP_SUFFIX";
}

if (!config.SCRIBE_HOST) {
    throw "need SCRIBE_HOST";
}
scribe = new Scribe(config.SCRIBE_HOST, config.SCRIBE_PORT, {"autoReconnect": true});
scribe.open();

var formatdate = function(){
    var d = new Date;
    // 2012-12-32 12:32:33 -480
    return d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate() + ' ' + d.getHours() + ':' + d.getMinutes() + ':' + d.getSeconds() + ' ' + d.getTimezoneOffset();
};

function long2ip (ip) {
  // http://kevin.vanzonneveld.net
  // +   original by: Waldo Malqui Silva
  // *     example 1: long2ip( 3221234342 );
  // *     returns 1: '192.0.34.166'
  if (!isFinite(ip))
    return false;

  return [ip >>> 24, ip >>> 16 & 0xFF, ip >>> 8 & 0xFF, ip & 0xFF].join('.');
}

var pad = function(n, length){
    var ret = '' + n;
    for (; ret.length < length; ) {
        ret = '0' + ret;
    }
    return ret;
};

var apachedate = function(){
    // [31/Jan/2013:16:57:47 -0500]
    var d = new Date;
    var month_locale = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    var zone = '';
    if (d.getTimezoneOffset() <= 0) {
        zone = '+' + pad(-100 * d.getTimezoneOffset() / 60, 4);
    } else {
        zone = '-' + pad(100 * d.getTimezoneOffset() / 60, 4);
    }
    return '[' + pad(d.getDate(), 2) + '/' + month_locale[d.getMonth()] + '/' + d.getFullYear() + ':' + pad(d.getHours(), 2) + ':' + pad(d.getMinutes(), 2) + ':' + pad(d.getSeconds(), 2) + ' ' + zone + ']';
};

var lb_core = {};
lb_core.cache = {};

lb_core.getBackendHost2 = function(host, port, callback){
    if (config.MAINPAGE_DOMAIN == host) {
        return callback({success: true, host: main_page_host, port: main_page_port});
    }

    // TODO: 要限內部網路才能做這件事
    if ('healthcheck' == host) {
        return callback({success: true, type: 'healthcheck'});
    }

    if (host.indexOf(config.APP_SUFFIX) > 0) {
        var project_name = host.split(config.APP_SUFFIX)[0];
        mysql_connection.query("SELECT * FROM `project` WHERE `name` = ?", [project_name], function(err, rows, fields){
            if (rows.length == 1) {
                lb_core._getNodesByProject(rows[0], callback);
                return;
            }
            return callback({success: false, message: 'Project not found'});
        });
    } else {
        mysql_connection.query("SELECT * FROM `project` WHERE `id` = (SELECT `project_id` FROM `custom_domain` WHERE `domain` = ?)", [host], function(err, rows, fields){
            if (rows.length == 1) {
                lb_core._getNodesByProject(rows[0], callback);
                return;
            }
            return callback({success: false, message: 'Domain not found'});
        });
    }
};

lb_core._getNodesByProject = function(project, callback){
    mysql_connection.query("SELECT * FROM `webnode` WHERE `project_id` = ? AND `status` IN (1, 10) AND `commit` = ?", [project.id, project.commit], function(err, rows, fields){
        if (!rows.length) {
            return lb_core._initNewNodes(project, callback);
        }
        var working_nodes = [];
        for (var i = 0; i < rows.length; i ++) {
            if (rows[i].status == 10) {
                working_nodes.push(rows[i]);
            }
        }
        if (working_nodes.length) {
            random_node = working_nodes[Math.floor(Math.random() * working_nodes.length)];
            return callback({success: true, host: random_node.ip, port: random_node.port, project: project});
        }
        setTimeout(function(){
            lb_core._getNodesByProject(project, callback);
        }, 500);
    });
};

lb_core._initNewNodes = function(project, callback){
    mysql_connection.query("SELECT * FROM `webnode` WHERE `project_id` = 0 AND `status` = 0", function(err, rows, fields){
        if (rows.length == 0) {
            return callback({success: false, message: 'No empty node'});
        }
        lb_core._initProjectOnNode(project, rows[Math.floor(Math.random() * rows.length)], callback);
    });
};

lb_core._initProjectOnNode = function(project, node, callback){
    mysql_connection.query("UPDATE `webnode` SET `project_id` = ?, `commit` = ?, `start_at` = ?, `status` = ? WHERE `ip` = ? AND `port` = ? AND `status` = 0", [project.id, project.commit, Math.floor((new Date()).getTime() / 1000), 1, node.ip, node.port], function(err, rows, fields){
        if (rows.affectedRows != 1) {
            return callback({success: false, message: 'Init new node failed'});
        }

        var ssh2 = new SSH2();
        var node_id = node.port - 20000;
        ssh2.on('ready', function(){
            ssh2.exec('clone ' + project.name + ' ' + node_id, function(err, stream){
                stream.on('end', function(){
                    ssh2.exec('restart-web ' + project.name + ' ' + node_id, function(err, stream){
                        stream.on('end', function(){
                            mysql_connection.query("UPDATE `webnode` SET `status` = 10 WHERE `ip` = ? AND `port` = ?", [node.ip, node.port], function(err, rows, fields){
                                if (rows.affectedRows != 1) {
                                    return callback({success: false, message: 'Init new node failed'});
                                }

                                // log node start
                                scribe.send('app-' + project.name + '-node', JSON.stringify({
                                    time: (new Date()).getTime() / 1000,
                                    ip: node.ip,
                                    port: node.port,
                                    commit: project.commit,
                                    type: 'web',
                                    status: 'start'
                                }));

                                callback({success: true, host: node.ip, port: node.port, project: project});
                            });
                            ssh2.end();
                        });
                    });
                });
            });
        });
        ssh2.connect({
            host: long2ip(node.ip),
            port: 22,
            username: 'root',
            privateKey: fs.readFileSync('/srv/config/web-key'),
        });

    });
};

lb_core.getBackendHost = function(host, port, callback){
    if (config.MAINPAGE_DOMAIN == host) {
        return callback({success: true, host: main_page_host, port: main_page_port});
    }
    // TODO: 要限內部網路才能做這件事
    if ('healthcheck' == host) {
        return callback({success: true, type: 'healthcheck'});
    }

    var selector_request = http.request({
        host: main_page_host,
        port: main_page_port,
        path: '/api/getnodes?domain=' + encodeURIComponent(host) + '&port=' + parseInt(port)
    }, function(selector_response) {
        var data = '';
        selector_response.on('data', function(chunk){
            data += chunk;
        });
        selector_response.on('end', function(){
            // { error: false, nodes: [ [ '10.146.23.10', '20006' ] ] }
            var json;
            try {
                json = JSON.parse(data);
            } catch (e) {
                return callback({success: false});
            }
            if (json.error) {
                return callback({success: false});
            }
            if (json.wait) {
                setTimeout(function(){
                    lb_core.getBackendHost(host, port, callback);
                }, 500);
                return;
            }
            return callback({success: true, host: json.nodes[0][0], port: json.nodes[0][1], project: json.project});
        });
        selector_response.on('close', function(){
            scribe.send('lb-error', formatdate() + ' selector_response_close ' + JSON.stringify(JSON.parse(data)));
        });
    }).end();
};

var http_main_request = http.createServer();
var https_options = {
    key: fs.readFileSync('/srv/config/middle2.key'),
    cert: fs.readFileSync('/srv/config/middle2.crt')
};
var https_main_request = https.createServer(https_options);
var request_count = 0;
var request_serial = 0;
var request_pools = {};
var recent_logs = [];
var start_time = (new Date()).getTime();

var http_request_callback = function(protocol){
    return function(main_request, main_response){
    var host = main_request.headers['host'];
    var port = 80;
    if (!host) {
        main_response.writeHead(302, {Location: 'http://' + config.MAINPAGE_DOMAIN + '/error/notfound'});
        main_response.end();
        return;
    }

    var current_request = request_serial;
    request_count ++;
    request_serial ++;
    request_pools[current_request] = {
        host: host,
        start_at: (new Date()).getTime(),
        from: main_request.headers['x-forwarded-for'],
        url: main_request.url,
    };

    if (host.match(/:/)) {
        port = parseInt(host.split(':')[1]);
        host = host.split(':')[0];
    }

    var main_request_pending_data = '';
    var main_request_is_end = false;

    var main_request_data = function(chunk){
        main_request_pending_data += chunk;
    };
    var main_request_end = function(){
        main_request_is_end = true;
    };
    if (!main_request.headers['x-forwarded-for']) {
        main_request.headers['x-forwarded-for'] = main_request.socket.remoteAddress;
        main_request.headers['x-forwarded-port'] = main_request.socket.address().port;
        if ('https' == protocol) {
            main_request.headers['x-forwarded-https'] = 'On';
        }
    }

    main_request.on('data', function(chunk){
        main_request_data(chunk);
    });
    main_request.on('end', function(){
        main_request_end();
    });
    main_request.on('close', function(){
    });

    lb_core.getBackendHost2(host, port, function(options){
        if (!options.success) {
            var referer = main_request.headers['referer'];
            if (typeof(referer) != 'string') {
                referer = '-';
            }
            var useragent = main_request.headers['user-agent'];
            if (typeof(useragent) != 'string') {
                useragent = '-';
            }
            var log = (host
                + ' ' + main_request.headers['x-forwarded-for']
                + ' - - ' + apachedate()
                + ' "' + main_request.method.toUpperCase() + ' ' + main_request.url + ' HTTP/' + main_request.httpVersion + '"'
                + ' 404 0'
                + ' "' + referer + '"'
                + ' "' + useragent + '"'
            ); 
            recent_logs.push(log);
            recent_logs = recent_logs.slice(recent_logs.length - 10);

            scribe.send('lb-notfound', log);
            main_response.writeHead(302, {Location: 'http://' + config.MAINPAGE_DOMAIN + '/error/notfound'});
            main_response.end();
            request_count --;
            delete(request_pools[current_request]);
            return;
        }

        if (options.type == 'healthcheck') {
            main_response.writeHead(200);
            main_response.write(JSON.stringify({
                status: 'OK',
                request_count: request_count,
                request_pools: request_pools,
                start_time: start_time,
                recent_logs: recent_logs,
            }));
            main_response.end();
            request_count --;
            delete(request_pools[current_request]);
            return;
        }

        var backend_host = options.host;
        var backend_port = options.port;
        var return_length = 0;

        if (options.project) {
            var now = Math.floor((new Date()).getTime() / 1000);
            memcache.increment('Project:access_count:' + options.project.id, 1);
            memcache.set('Project:access_at:' + options.project.id,  now);
            memcache.increment('WebNode:access_count:' + options.host + ':' + options.port);
            memcache.set('WebNode:access_at:' + options.host + ':' + options.port, now);
        }

        var backend_request = http.request({
            hostname: backend_host,
            port: backend_port,
            host: host,
            method: main_request.method,
            path: main_request.url,
            headers: main_request.headers,
            agent: false
        }, function(backend_response){
            main_response.writeHead(backend_response.statusCode, backend_response.headers);
            backend_response.on('data', function(chunk){
                main_response.write(chunk);
                return_length += chunk.length;
            });

            backend_response.on('end', function(){
                if ('object' == typeof(options.project)) {
                    var referer = main_request.headers['referer'];
                    if (typeof(referer) != 'string') {
                        referer = '-';
                    }
                    var useragent = main_request.headers['user-agent'];
                    if (typeof(useragent) != 'string') {
                        useragent = '-';
                    }
                    var log = (host
                        + ' ' + main_request.headers['x-forwarded-for']
                        + ' - - ' + apachedate()
                        + ' "' + main_request.method.toUpperCase() + ' ' + main_request.url + ' HTTP/' + main_request.httpVersion + '"'
                        + ' ' + backend_response.statusCode + ' ' + return_length 
                        + ' "' + referer + '"'
                        + ' "' + useragent + '"'); 
                    scribe.send('app-' + options.project.name, log);
                    recent_logs.push(log);
                    recent_logs = recent_logs.slice(recent_logs.length - 10);
                }
                main_response.end();
                request_count --;
                delete(request_pools[current_request]);
            });

            backend_response.on('close', function(){
                scribe.send('lb-error', formatdate() + ' backend_response_close');
            });
        });

        backend_request.on('error', function(e){
            scribe.send('lb-error', formatdate() + ' backend_request_error' + ' ' + host + ' ' + backend_host + ' ' + backend_port + ' ' + e);
            main_response.end();
            request_count --;
            delete(request_pools[current_request]);
        });

        main_request_data = function(chunk){
            backend_request.write(chunk);
        };
        main_request_end = function(){
            backend_request.end();
        };
        main_request_data(main_request_pending_data);

        if (main_request_is_end) {
            main_request_end();
        }

        return;
    });
};
};

https_main_request.on('request', http_request_callback('https')).listen(443, 0);
http_main_request.on('request', http_request_callback('http')).listen(80, 0);
