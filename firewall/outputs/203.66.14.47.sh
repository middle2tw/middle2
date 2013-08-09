#!/bin/sh
iptables -F
iptables -X
iptables -Z
iptables -P INPUT DROP
iptables -P OUTPUT ACCEPT
iptables -P FORWARD ACCEPT
iptables -A INPUT -i lo -j ACCEPT
iptables -A INPUT -m state --state RELATED,ESTABLISHED -j ACCEPT
# allow dev, mainpage, loadbalancer from categories ALL, node
iptables -A INPUT -p tcp -s 210.65.10.110 --dport 22 -j ACCEPT
# allow loadbalancer from categories node
iptables -A INPUT -p tcp -s 210.65.10.110 --dport 20001:29999 -j ACCEPT
