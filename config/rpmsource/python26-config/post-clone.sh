#!/bin/sh

if [ -e /srv/web/requirements.txt ]; then
        cd /srv/web
        pip --proxy package-proxy-p.hisoku.ronny.tw:3128 --log /srv/logs/pip.log install -r requirements.txt
fi
