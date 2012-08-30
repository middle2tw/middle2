#!/bin/sh
/usr/sbin/php-fpm
/usr/local/apache2/bin/apachectl graceful
