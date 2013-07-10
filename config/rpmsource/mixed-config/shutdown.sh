#!/bin/sh

if [ -f "/srv/logs/php-fpm.pid" ]; then
    kill `cat /srv/logs/php-fpm.pid`
fi

if [ -f "/srv/logs/httpd.pid" ]; then
    kill `cat /srv/logs/httpd.pid`
fi

kill -1
