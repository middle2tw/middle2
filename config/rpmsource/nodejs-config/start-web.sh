#!/bin/sh

# build /srv/env.conf
env PORT=`cat /etc/nodejs-port.conf` /usr/local/bin/node /srv/web/web.js > /dev/null &

START_AT=`date +%s`
END_AT=`expr 10 + $START_AT`

CHECK_FAIL="1"
while [ $CHECK_FAIL -eq "1" -a $END_AT -gt `date +%s` ]
do
        echo '' | nc 0 `cat /etc/nodejs-port.conf`
        CHECK_FAIL=$?
        sleep 1
done
