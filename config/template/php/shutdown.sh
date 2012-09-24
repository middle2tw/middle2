#!/bin/sh

if [ -f "/var/run/php-fpm.pid" ]; then
    kill `cat /var/run/php-fpm.pid`
fi

if [ -f "/usr/local/apache2/logs/httpd.pid" ]; then
    kill `cat /usr/local/apache2/logs/httpd.pid`
fi
