var http = require('http');
var Scribe = require('scribe').Scribe;
scribe = new Scribe("scribe.hisoku.ronny.tw", 1463, {"autoReconnect": true});
scribe.open();

if (process.argv.length < 4) {
    throw "Usage: node loadbalancer.js [server-ip] [server-port]";
}

var formatdate = function(){
    var d = new Date;
    // 2012-12-32 12:32:33 -480
    return d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate() + ' ' + d.getHours() + ':' + d.getMinutes() + ':' + d.getSeconds() + ' ' + d.getTimezoneOffset();
};

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

hisoku.getBackendHost = function(host, port, callback){
    if ('hisoku.ronny.tw' == host) {
        return callback({success: true, host: 'main-p.hisoku.ronny.tw', port: 9999});
    }
    if ('healthcheck' == host) {
        return callback({success: true, type: 'healthcheck'});
    }

    var selector_request = http.request({
        host: 'main-p.hisoku.ronny.tw',
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
            return callback({success: true, host: json.nodes[0][0], port: json.nodes[0][1], project: json.project});
        });
        selector_response.on('close', function(){
            scribe.send('lb-error', formatdate() + ' selector_response_close ' + JSON.stringify(JSON.parse(data)));
        });
    }).end();
};

var main_request = http.createServer();

main_request.on('request', function(main_request, main_response){
    var host = main_request.headers['host'];
    var port = 80;
    if (!host) {
        main_response.writeHead(302, {Location: 'http://hisoku.ronny.tw/error/notfound'});
        main_response.end();
        return;
    }

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
            scribe.send('lb-notfound', log);
            main_response.writeHead(302, {Location: 'http://hisoku.ronny.tw/error/notfound'});
            main_response.end();
            return;
        }

        if (options.type == 'healthcheck') {
            main_response.writeHead(200);
            main_response.write('OK');
            main_response.end();
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
                }
                main_response.end();
            });

            backend_response.on('close', function(){
                scribe.send('lb-error', formatdate() + ' backend_response_close');
            });
        });

        backend_request.on('error', function(e){
            scribe.send('lb-error', formatdate() + ' backend_request_error' + ' ' + host + ' ' + backend_host + ' ' + backend_port + ' ' + e);
            main_response.end();
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
