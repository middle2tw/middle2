var http = require('http');

if (process.argv.length < 4) {
    throw "Usage: node loadbalancer.js [server-ip] [server-port]";
}

var hisoku = {};
hisoku.cache = {};

hisoku.getBackendHost = function(host, port, callback){
    if ('hisoku.ronny.tw' == host) {
        return callback('localhost', 9999);
    }

    var selector_request = http.request({
        host: 'localhost',
        port: 9999,
        path: '/api/getnodes?domain=' + encodeURIComponent(host) + '&port=' + parseInt(port)
    }, function(selector_response) {
        var data = '';
        selector_response.on('data', function(chunk){
            data += chunk;
        });
        selector_response.on('end', function(){
            // { error: false, nodes: [ [ '10.146.23.10', '20006' ] ] }
            var json = JSON.parse(data);
            if (json.error) {
                // TODO: error
            }
            return callback(json.nodes[0][0], json.nodes[0][1]);
        });
        selector_response.on('close', function(){
            // TODO : error
            console.log(JSON.parse(data));
        });
    }).end();
};

var main_request = http.createServer();

main_request.on('request', function(main_request, main_response){
    var host = main_request.headers['host'];
    var port = 80;
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
    main_request.headers['X-Forwarded-For'] = main_request.socket.remoteAddress;
    console.log(host + ' ' + main_request.url);

    main_request.on('data', function(chunk){
        main_request_data(chunk);
    });
    main_request.on('end', function(){
        main_request_end();
    });
    main_request.on('close', function(){
    });

    hisoku.getBackendHost(host, port, function(backend_host, backend_port){
        console.log(host + ' ' + backend_host + ' ' + backend_port);
        delete(main_request.headers['host']);

        var backend_request = http.request({
            host: backend_host,
            port: backend_port,
            hostname: host,
            method: main_request.method,
            path: main_request.url,
            headers: main_request.headers,
            agent: false
        }, function(backend_response){
            main_response.writeHead(backend_response.statusCode, backend_response.headers);
            backend_response.on('data', function(chunk){
                main_response.write(chunk);
            });

            backend_response.on('end', function(){
                main_response.end();
            });

            backend_response.on('close', function(){
                // TODO: log error
            });
        });

        backend_request.on('error', function(e){
            // TODO: log error
            console.log('error');
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
