#!/bin/sh

if [ -d "/srv/web/_public" ]; then
        sed -i "s/DocumentRoot \/srv\/web/DocumentRoot \/srv\/web\/_public/" /etc/httpd/extra/node.conf
fi
