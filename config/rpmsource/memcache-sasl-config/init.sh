#!/bin/sh

PORT=$1
USER=$2
PASSWORD=$3

echo ${PASSWORD} | saslpasswd2 -a memcached -p -c ${USER}
chmod 644 /etc/sasldb2
