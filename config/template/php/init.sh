#!/bin/sh

port=$1

sed -i 's/^Listen 80$/Listen '$1'/' /usr/local/apache2/conf/httpd.conf
