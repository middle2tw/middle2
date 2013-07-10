#!/bin/sh

port=$1

sed -i 's/^Listen 80$/Listen '$port'/' /etc/httpd/httpd.conf
sed -i 's#/var/logs#/srv/logs#g' /etc/httpd/httpd.conf
echo "\nInclude /etc/httpd/extra/node.conf" >> /etc/httpd/httpd.conf
echo $port > /etc/port.conf
