var http = require('http');
var https = require('https');
var Scribe = require('scribe').Scribe;
var Memcache = require('memcache');
var mysql = require('mysql');
var SSH2 = require('ssh2');
var fs = require('fs');
var crypto = require('crypto');
var constants = require('constants');

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
fs.writeFile('/tmp/middle2.pid', process.pid);

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

var project_connections = {};
var mapping_cache = {'project-name-to-id': {}, 'project-domain-to-id': {}, 'project-to-webnode': {}};

lb_core.getBackendHost2 = function(host, port, current_request, callback){
    if (config.MAINPAGE_DOMAIN == host) {
        return callback({success: true, host: main_page_host, port: main_page_port, is_main_page: true});
    }

    if (host == config.TEST_HOST) {
        return callback({success: true, host: 'localhost', port: 5566});
    }

    // TODO: 要限內部網路才能做這件事
    if ('healthcheck' == host) {
        return callback({success: true, type: 'healthcheck'});
    }

    // TODO: 要限內部網路才能做這件事
    if ('cleancache' == host) {
        mapping_cache = {'project-name-to-id': {}, 'project-domain-to-id': {}, 'project-to-webnode': {}};
        return callback({success: true, type: 'healthcheck'});
    }

    if (host.indexOf(config.APP_SUFFIX) > 0) {
        var project_name = host.split(config.APP_SUFFIX)[0];
        request_pools[current_request].state = 'get-project-from-domain';
        var project = mapping_cache['project-name-to-id'][project_name];
        if ('undefined' !== typeof(project)) {
            if (project) {
                return lb_core._getNodesByProject(project, current_request, callback);
            }
            return callback({success: false, message: 'Project not found', code: 404});
        }

        mysql_connection.query("SELECT * FROM `project` WHERE `name` = ?", [project_name], function(err, rows, fields){
            mapping_cache['project-name-to-id'][project_name] = rows[0];
            request_pools[current_request].state = 'get-project-from-domain-done';
            if (rows.length == 1) {
                lb_core._getNodesByProject(rows[0], current_request, callback);
                return;
            }
            return callback({success: false, message: 'Project not found', code: 404});
        });
    } else {
        request_pools[current_request].state = 'get-project-from-domain';
        var project = mapping_cache['project-domain-to-id'][host];
        if ('undefined' !== typeof(project)) {
            if (project) {
                return lb_core._getNodesByProject(project, current_request, callback);
            }
            return callback({success: false, message: 'Project not found', code: 404});
        }
        mysql_connection.query("SELECT * FROM `project` WHERE `id` = (SELECT `project_id` FROM `custom_domain` WHERE `domain` = ?)", [host], function(err, rows, fields){
            request_pools[current_request].state = 'get-project-from-domain-done';
            mapping_cache['project-domain-to-id'][host] = rows[0];
            if (rows.length == 1) {
                lb_core._getNodesByProject(rows[0], current_request, callback);
                return;
            }
            return callback({success: false, message: 'Domain not found', code: 404});
        });
    }
};

lb_core._getNodesByProject = function(project, current_request, callback){
    request_pools[current_request].state = 'get-webnode-from-project';
    request_pools[current_request].project = project.name;
    var working_nodes = mapping_cache['project-to-webnode'][project.id + '-' + project.commit];
    if ('undefined' !== typeof(working_nodes)) {
        var random_node = working_nodes[Math.floor(Math.random() * working_nodes.length)];
        return callback({success: true, host: random_node.ip, port: random_node.port, project: project});
    }

    // 1 - STATUS_WEBPROCESSING, 10 - STATUS_WEBNODE
    mysql_connection.query("SELECT * FROM `webnode` WHERE `project_id` = ? AND `status` IN (1, 10) AND `commit` = ?", [project.id, project.commit], function(err, rows, fields){
        request_pools[current_request].state = 'get-webnode-from-project-done';
        if (!rows.length) {
            request_pools[current_request].state = 'init-new-node';
            return lb_core._initNewNodes(project, callback);
        }
        var working_nodes = [];
        var no_processing_nodes = true;
        for (var i = 0; i < rows.length; i ++) {
            if (rows[i].status == 10) {
                working_nodes.push(rows[i]);
            } else {
                no_processing_nodes = false;
            }
        }

        // 沒有 processing node 的情況下再 cache
        if (working_nodes.length && no_processing_nodes) {
            mapping_cache['project-to-webnode'][project.id + '-' + project.commit] = working_nodes;
        }

        if (working_nodes.length) {
            random_node = working_nodes[Math.floor(Math.random() * working_nodes.length)];
            return callback({success: true, host: random_node.ip, port: random_node.port, project: project});
        }
        setTimeout(function(){
            lb_core._getNodesByProject(project, current_request, callback);
        }, 500);
    });
};

var init_pools = {};
var init_running = false;

var run_init = function(){
    init_running = true;
    for (var id in init_pools) {
        var project = init_pools[id].project;
        var callbacks = init_pools[id].callbacks;

        var callback = (function(project, callbacks){
            return function(ret){
                callbacks.map(function(callback){
                        callback(ret);
                });
                delete(init_pools[project.id]);
                run_init();
            };
        })(project, callbacks);

        mysql_connection.query("SELECT * FROM `webnode` WHERE `project_id` = 0 AND `status` = 0", function(err, rows, fields){
            if (rows.length == 0) {
                callbacks.map(function(callback){
                        callback({success: false, message: 'No empty node', code: 503});
                });
                return;
            }
            lb_core._initProjectOnNode(project, rows[Math.floor(Math.random() * rows.length)], callback);
        });
        return;
    }
    init_running = false;
};

lb_core._initNewNodes = function(project, callback){
    if ('undefined' !== typeof(init_pools[project.id])) {
        init_pools[project.id].callbacks.push(callback);
    } else {
        init_pools[project.id] = { project: project, callbacks: [callback] };
    }

    if (!init_running) {
        run_init();
    }

};


lb_core._initProjectOnNode = function(project, node, callback){
    mysql_connection.query("UPDATE `webnode` SET `project_id` = ?, `commit` = ?, `start_at` = ?, `status` = ? WHERE `ip` = ? AND `port` = ? AND `status` = 0", [project.id, project.commit, Math.floor((new Date()).getTime() / 1000), 1, node.ip, node.port], function(err, rows, fields){
        if (rows.affectedRows != 1) {
            return callback({success: false, message: 'Init new node failed', code: 503});
        }

        var ssh2 = new SSH2.Client();
        var node_id = node.port - 20000;
        ssh2.on('ready', function(){
            ssh2.exec('clone ' + project.name + ' ' + node_id, function(err, stream){
                stream.on('exit', function(){
                    ssh2.exec('restart-web ' + project.name + ' ' + node_id, function(err, stream){
                        stream.on('exit', function(){
                            mysql_connection.query("UPDATE `webnode` SET `status` = 10 WHERE `ip` = ? AND `port` = ?", [node.ip, node.port], function(err, rows, fields){
                                if (rows.affectedRows != 1) {
                                    return callback({success: false, message: 'Init new node failed', code: 503});
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
        }).connect({
            host: long2ip(node.ip),
            port: 22,
            username: 'root',
            privateKey: fs.readFileSync('/srv/config/web-key'),
        });

    });
};

var secureContext = {};

var renewSSLkeys = function() {
    console.log('renewSSLkeys');
    mysql_connection.query("SELECT * FROM `ssl_keys`", function(err, rows, fields){
        for (var i = 0; i < rows.length; i ++) {
            var row = rows[i];
            secureContext[row.domain] = crypto.createCredentials(JSON.parse(row.config)).context;
            https_main_request.addContext(row.domain, secureContext[row.domain]);
        }
    });
}
renewSSLkeys();

process.on('SIGHUP', function() {
        renewSSLkeys();
});

var http_main_request = http.createServer();
var https_options = {
    ca: [
        fs.readFileSync('/srv/config/middle2.ca.crt'),
        fs.readFileSync('/srv/config/middle2.ca.2.crt'),
        fs.readFileSync('/srv/config/middle2.ca.3.crt')
    ],
    key: fs.readFileSync('/srv/config/middle2.key'),
    cert: fs.readFileSync('/srv/config/middle2.crt'),
    secureOptions: constants.SSL_OP_NO_SSLv3 | constants.SSL_OP_NO_SSLv2,
    SNICallback: function(domain) {
        return secureContext[domain];
    }
};

var https_main_request = https.createServer(https_options);
var request_count = 0;
var request_serial = 0;
var request_pools = {};
var recent_logs = [];
var start_time = (new Date()).getTime();

var http_request_callback = function(protocol){
    return function(main_request, main_response){
    var le_matches = main_request.url.match("/\.well-known/acme-challenge/([0-9a-zA-Z-_]*)");
    if (le_matches && fs.existsSync('/home/srwang/work/letsencrypt.sh/.acme-challenges/' + le_matches[1])) {
        main_response.write(fs.readFileSync('/home/srwang/work/letsencrypt.sh/.acme-challenges/' + le_matches[1]));
        main_response.end();
        return;
    }
    var host = main_request.headers['host'];
    var port = 80;
    if (!host) {
        main_response.writeHead(400);
        main_response.write('400 Bad Request');
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
        state: 'init',
    };

    if (host.match(/:/)) {
        port = parseInt(host.split(':')[1]);
        host = host.split(':')[0];
    }

    var main_request_pending_data = [];
    var backend_cb = null;

    var main_request_data = function(chunk){
        if ('function' == typeof(backend_cb)) {
            while (main_request_pending_data.length) {
                backend_cb(main_request_pending_data.shift());
            }
            if (chunk !== null) {
                backend_cb(chunk);
            }
            return;
        }
        if (chunk !== null) {
            main_request_pending_data.push(chunk);
        }
    };

    var main_request_end = function(){
        main_request_data(false);
    };

    if (!main_request.headers['x-forwarded-for']) {
        main_request.headers['x-forwarded-for'] = main_request.socket.remoteAddress;
        main_request.headers['x-real-ip'] = main_request.socket.remoteAddress;
        main_request.headers['x-forwarded-port'] = main_request.socket.address().port;
        if ('https' == protocol) {
            main_request.headers['x-forwarded-https'] = 'On';
            main_request.headers['x-forwarded-proto'] = 'https';
            main_request.headers['x-scheme'] = 'https';
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

    lb_core.getBackendHost2(host, port, current_request, function(options){
        request_pools[current_request].state = 'load-from-backend';
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
                + ' ' + options.code + ' 0'
                + ' "' + referer + '"'
                + ' "' + useragent + '"'
            ); 
            recent_logs.push(log);
            recent_logs = recent_logs.slice(recent_logs.length - 10);

            scribe.send('lb-notfound', log);
            main_response.writeHead(options.code);
            main_response.write(options.message);
            if (options.code >= 500) {
                scribe.send('500-log', log);
            }
            main_response.end();
            request_count --;
            delete(request_pools[current_request]);
            return;
        }

        if (options.type == 'healthcheck') {
            main_response.writeHead(200);

            // recount project_connections
            new_project_connections = {};
            for (var id in request_pools) {
                if ('undefined' !== typeof(request_pools[id].project)) {
                    n = request_pools[id].project;
                    if ('undefined' === typeof(new_project_connections[n])) {
                        new_project_connections[n] = 0;
                    }
                    new_project_connections[n] ++;
                }
            }
            project_connections = new_project_connections;

            var ret = {
                status: 'OK',
                request_serial: request_serial,
                request_count: request_count,
                request_pools: request_pools,
                start_time: start_time,
                recent_logs: recent_logs,
                project_connections: project_connections,
            };
            if (main_request.url.indexOf('mapping_cache') >= 0) {
                ret['mapping_cache'] = mapping_cache;
            }
            main_response.write(JSON.stringify(ret));
            main_response.end();
            request_count --;
            delete(request_pools[current_request]);
            return;
        }

        var backend_host = options.host;
        var backend_port = options.port;
        var return_length = 0;

        if (options.project) {
            if (project_connections[options.project.name] > 20) {
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
                        + ' 503 0'
                        + ' "' + referer + '"'
                        + ' "' + useragent + '"'
                        ); 
                recent_logs.push(log);
                recent_logs = recent_logs.slice(recent_logs.length - 10);

                main_response.writeHead(503);
                main_response.write('503 Too many connections');
                scribe.send('500-log', log);
                main_response.end();
                request_count --;
                delete(request_pools[current_request]);
                return;
            }

            var now = Math.floor((new Date()).getTime() / 1000);
            memcache.increment('Project:access_count:' + options.project.id, 1);
            memcache.set('Project:access_at:' + options.project.id,  now);
            memcache.increment('WebNode:access_count:' + options.host + ':' + options.port);
            memcache.set('WebNode:access_at:' + options.host + ':' + options.port, now);
            if (typeof(project_connections[options.project.name]) === 'undefined') {
                project_connections[options.project.name] = 0;
            }
            project_connections[options.project.name] ++;
        }


        main_request.headers['connection'] = 'close';

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
                request_pools[current_request].state = 'load-from-backend-done';
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

                if ('object' == typeof(options.project)) {
                    project_connections[options.project.name] --;
                    scribe.send('app-' + options.project.name, log);
                } else if (options.is_main_page) {
                    scribe.send('mainpage', log);
                }

                if (backend_response.statusCode >= 500) {
                    scribe.send('500-log', log);
                }

                recent_logs.push(log);
                recent_logs = recent_logs.slice(recent_logs.length - 10);
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

        var write_to_backend = function(data){
            if (data === false) {
                backend_request.end();
                return;
            }
            backend_request.write(data);
        };

        backend_cb = write_to_backend;
        main_request_data(null);

        return;
    });
};
};

https_main_request.on('request', http_request_callback('https')).listen(443, 0);
http_main_request.on('request', http_request_callback('http')).listen(80, 0);
