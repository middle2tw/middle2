#!/bin/sh

pip install gunicorn
if [ -e /srv/web/requirements.txt ]; then
        cd /srv/web
        pip install -r requirements.txt
fi
