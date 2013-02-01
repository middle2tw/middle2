#!/bin/sh

# build /srv/env.conf
env PORT=`cat /etc/nodejs-port.conf` /usr/local/bin/node /srv/web/web.js > /dev/null &
# TODO: wait until port ok
sleep 3
