#!/bin/sh

export LOG_FILE=/srv/logs/web.log
export PORT=`cat /etc/port.conf`
export HOME=/srv/web

cd /srv/web

if [ -f "/srv/web/Procfile" ]; then
    `cat /srv/web/Procfile | grep '^web:' |  awk '{print substr($0, 5); }' | sed "s/\\$PORT/$PORT/"` > ${LOG_FILE} 2>&1 &
elif [ -f "/srv/web/manage.py" ]; then
 # python django
    python ./manage.py runserver 0.0.0.0:`cat /etc/port.conf` --noreload > ${LOG_FILE} 2>&1 &
elif [ -f "/srv/web/app.py" ]; then
# python app.py
    gunicorn app:app -b 0.0.0.0:`cat /etc/port.conf` > ${LOG_FILE} &
elif [ -f "/srv/web/web.rb" ]; then
# ruby
    export RACK_ENV=production
    ruby web.rb -p `cat /etc/port.conf` > ${LOG_FILE} 2>&1 &
#elif [ -f "/srv/web/index.php" ]; then
else
# build /srv/env.conf
    env | awk -F '=' '{print "env[" $1 "]=" $2}' > /srv/logs/env.conf

    # start php-fpm
    /usr/sbin/php-fpm

    # start apache
    /usr/sbin/apachectl graceful
fi

START_AT=`date +%s`
END_AT=`expr 300 + $START_AT`

#CHECK_FAIL="1"
#while [ $CHECK_FAIL -gt "0" -a $END_AT -gt `date +%s` ]
#do
#        wget --timeout=5 http://0:`cat /etc/port.conf` -O /dev/null
#        CHECK_FAIL=$?
#        sleep 1
#done
