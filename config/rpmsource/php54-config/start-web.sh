#!/bin/sh

# build /srv/env.conf
env | awk -F '=' '{print "env[" $1 "]=" $2}' > /srv/logs/env.conf

# start php-fpm
/usr/sbin/php-fpm

# start apache
/usr/sbin/apachectl graceful
