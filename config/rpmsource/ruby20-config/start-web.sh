#!/bin/sh

# build /srv/env.conf
cd /srv/web

env RACK_ENV=production PORT=`cat /etc/port.conf` HOME=/srv/web ruby web.rb -p `cat /etc/port.conf` > /srv/logs/web.log 2>&1 &

START_AT=`date +%s`
END_AT=`expr 300 + $START_AT`

CHECK_FAIL="1"
while [ $CHECK_FAIL -gt "0" -a $END_AT -gt `date +%s` ]
do
        wget --timeout=5 http://0:`cat /etc/port.conf` -O /dev/null
        CHECK_FAIL=$?
        sleep 1
done
