#!/bin/sh
iptables -F
iptables -X
iptables -Z
iptables -P INPUT DROP
iptables -P OUTPUT ACCEPT
iptables -P FORWARD ACCEPT
iptables -A INPUT -i lo -j ACCEPT
iptables -A INPUT -m state --state RELATED,ESTABLISHED -j ACCEPT
# allow dev from categories ALL
iptables -A INPUT -p tcp -s 106.187.102.58 --dport 22 -j ACCEPT
# allow all from categories pgsql
iptables -A INPUT -p tcp --dport 5432 -j ACCEPT
sleep 30
iptables -F
iptables -X
iptables -Z
iptables -P INPUT ACCEPT
iptables -P OUTPUT ACCEPT
iptables -P FORWARD ACCEPT
