var http = require('http');

if (process.argv.length < 4) {
    throw "Usage: node loadbalancer.js [server-ip] [server-port]";
}

var hisoku = {};
hisoku.getBackendHost = function(host, port, callback){
    if ('hisoku.ronny.tw' == host) {
        return callback('localhost', 9999);
    }
};

http.createServer(function(main_request, main_response){
    var host = main_request.headers['host'];
    var port = 80;
    if (host.match(/:/)) {
        port = parseInt(host.split(':')[1]);
        host = host.split(':')[0];
    }
    main_request.headers['X-Forwarded-For'] = main_request.socket.remoteAddress;

    hisoku.getBackendHost(host, port, function(backend_host, backend_port){
        var backend_request = http.request({
            host: backend_host,
            port: backend_port,
            hostname: host,
            method: main_request.method,
            path: main_request.uri,
            headers: main_request.headers
        }, function(backend_response){
            main_request.writeHead(backend_response.statusCode, backend_response.headers);
            backend_response.on('data', function(chunk){
                main_response.write(chunk);
            });

            backend_response.on('end', function(){
                main_response.end();
            });

            backend_response.on('close', function(){
                // TODO: log error
                main_response.end();
            });
        });

        backend_request.on('error', function(e){
            // TODO: log error
            main_response.end();
        });

        main_request.on('data', function(chunk){
            backend_request.write(chunk);
        });
        return;
    });
}).listen(process.argv[3], process.argv[2]);
