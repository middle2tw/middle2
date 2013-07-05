#!/bin/sh

if [ -e /srv/web/Gemfile ]; then
        cd /srv/web
        bundle install
fi
