#!/bin/sh

if [ -e /srv/web/requirements.txt ]; then
        cd /srv/web
        pip --log /srv/logs/pip.log install -r requirements.txt --build /tmp/pip-build
fi
