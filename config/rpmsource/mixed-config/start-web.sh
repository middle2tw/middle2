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
HTTP_CODE=0

while [ $END_AT -gt `date +%s` ]
do
        HTTP_CODE=`curl --connect-timeout 3 --stderr /dev/null --include http://0:$PORT | head -n 1 | awk '{print $2}'`
        echo $HTTP_CODE | grep '^[0-9]\+$' > /dev/null
        if [ "$?" -eq "1" ]; then
            sleep 1
        else
            if [ "$HTTP_CODE" = "" ]; then
                sleep 1
            elif [ $HTTP_CODE -eq 0 -o $HTTP_CODE -eq 500 ]; then
                sleep 1
            else
                END_AT=0
            fi
        fi
done

#TODO 失敗的話要讓 loadbalancer 知道, 可以從 END_AT 判斷
