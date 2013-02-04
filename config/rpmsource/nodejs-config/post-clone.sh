#!/bin/sh

if [ -e /srv/web/package.json ]; then
        cd /srv/web
        /usr/local/bin/npm install -l
fi
