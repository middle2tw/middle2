#!/bin/sh

port=$1

sed -i 's/^Listen 80$/Listen '$port'/' /etc/httpd.conf
sed -i 's#/var/logs#/srv/logs#g' /etc/httpd.conf
echo -e "\nInclude /etc/extra/node.conf" >> /etc/httpd.conf
