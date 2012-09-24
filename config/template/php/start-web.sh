#!/bin/sh

# build /usr/local/apache2/conf/extra/node.conf
log_category=$1
echo $log_category > /srv/log_category

# build /srv/env.conf
env | awk -F '=' '{print "env[" $1 "]=" $2}' > /srv/env.conf

# start php-fpm
/usr/sbin/php-fpm

# start apache
/usr/local/apache2/bin/apachectl graceful
