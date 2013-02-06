#!/bin/sh

# build /srv/env.conf
cd /srv/web
env PORT=`cat /etc/port.conf` gunicorn app:app -b 0.0.0.0:`cat /etc/port.conf` > /dev/null &

START_AT=`date +%s`
END_AT=`expr 30 + $START_AT`

CHECK_FAIL="1"
while [ $CHECK_FAIL -eq "1" -a $END_AT -gt `date +%s` ]
do
        echo '' | nc 0 `cat /etc/port.conf`
        CHECK_FAIL=$?
        sleep 1
done
