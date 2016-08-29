#!/bin/sh

if [ -d "/srv/web/_public" ]; then
        sed -i "s/\/srv\/web/\/srv\/web\/_public/" /etc/apache2/sites-enabled/000-default.conf
fi
