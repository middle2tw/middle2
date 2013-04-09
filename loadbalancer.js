var http = require('http');
var Scribe = require('scribe').Scribe;
var main_page_host = 'main-p.hisoku.ronny.tw';

var mysql = require('mysql');

var SSH2 = require('ssh2');

scribe = new Scribe("scribe.hisoku.ronny.tw", 1463, {"autoReconnect": true});
scribe.open();

if (process.argv.length < 4) {
    throw "Usage: node loadbalancer.js [server-ip] [server-port]";
}

var loadConfig = function(){
    var content = require('fs').readFileSync('/srv/config/config.php');
    var regex = /putenv\(\'([^=]*)=([^\']*)\'\);/g;

    var match;
    var ret = {};
    while (match = regex.exec(content)) {
        ret[match[1]] = match[2];
    }
    return ret;
};

var config = loadConfig();
var mysql_connection = mysql.createConnection({
      host     : config.MYSQL_HOST,
      user     : config.MYSQL_USER,
      password : config.MYSQL_PASS,
      database : config.MYSQL_DATABASE,
});

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

var hisoku = {};
hisoku.cache = {};

hisoku._getBackendHost = function(host, port, callback){
    if ('hisoku.ronny.tw' == host) {
        return callback({success: true, host: main_page_host, port: 9999});
    }

    // TODO: 要限內部網路才能做這件事
    if ('healthcheck' == host) {
        return callback({success: true, type: 'healthcheck'});
    }

    if (host.match(/\.hisokuapp\.ronny\.tw/)) {
        var project_name = host.match(/([^.])\.hisokuapp\.ronny\.tw/)[1];
        mysql_connection.query("SELECT * FROM `project` WHERE `name` = ?", [project_name], function(err, rows, fields){
            if (rows.length == 1) {
                hisoku._getNodesByProject(rows[0], callback);
                return;
            }
            return callback({success: false, message: 'Project not found'});
        });
    } else {
        mysql_connection.query("SELECT * FROM `project` WHERE `id` = (SELECT `project_id` FROM `custom_domain` WHERE `domain` = ?)", [host], function(err, rows, fields){
            if (rows.length == 1) {
                hisoku._getNodesByProject(rows[0], callback);
                return;
            }
            return callback({success: false, message: 'Domain not found'});
        });
    }
};

hisoku._getNodesByProject = function(project, callback){
    mysql_connection.query("SELECT * FROM `webnode` WHERE `project_id` = ? AND `status` = 10 AND `commit` = ?", [project.id, project.commit], function(err, rows, fields){
        if (rows.length) {
            return callback({success: true, host: rows[0].ip, port: rows[0].port, project: project.name});
        }
        hisoku._initNewNodes(project, callback);
    });
};

hisoku._initNewNodes = function(project, callback){
    mysql_connection.query("SELECT * FROM `webnode` WHERE `project_id` = 0 AND `status` = 0", function(err, rows, fields){
        if (rows.length == 0) {
            return callback({success: false, message: 'No empty node'});
        }
        hisoku._initProjectOnNode(project, rows[Math.floor(Math.random() * rows.length)], callback);
    });
};

hisoku._initProjectOnNode = function(project, node, callback){
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
                                callback({success: true, host: node.ip, port: node.port, project: project.name});
                            });
                        });
                    });
                });
            });
        });
        ssh2.connect({
            host: long2ip(node.ip),
            port: 22,
            username: 'root',
            privateKey: require('fs').readFileSync('/srv/config/web-key'),
        });

    });
};

hisoku.getBackendHost = function(host, port, callback){
    if ('hisoku.ronny.tw' == host) {
        return callback({success: true, host: main_page_host, port: 9999});
    }
    // TODO: 要限內部網路才能做這件事
    if ('healthcheck' == host) {
        return callback({success: true, type: 'healthcheck'});
    }

    var selector_request = http.request({
        host: main_page_host,
        port: 9999,
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
                    hisoku.getBackendHost(host, port, callback);
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

var main_request = http.createServer();
var request_count = 0;
var request_serial = 0;
var request_pools = {};
var recent_logs = [];
var start_time = (new Date()).getTime();

main_request.on('request', function(main_request, main_response){
    var host = main_request.headers['host'];
    var port = 80;
    if (!host) {
        main_response.writeHead(302, {Location: 'http://hisoku.ronny.tw/error/notfound'});
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
    }

    main_request.on('data', function(chunk){
        main_request_data(chunk);
    });
    main_request.on('end', function(){
        main_request_end();
    });
    main_request.on('close', function(){
    });

    hisoku.getBackendHost(host, port, function(options){
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
            main_response.writeHead(302, {Location: 'http://hisoku.ronny.tw/error/notfound'});
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
        //console.log(host + ' ' + backend_host + ' ' + backend_port);

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
                if ('string' == typeof(options.project)) {
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
                    scribe.send('app-' + options.project, log);
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
}).listen(process.argv[3], process.argv[2]);
