#!/bin/sh
echo "1" > /proc/sys/net/ipv4/ip_forward
modprobe ip_tables
modprobe ip_nat_ftp
modprobe ip_nat_irc
/sbin/iptables -t nat -A POSTROUTING -o eth0 -s 10.0.0.0/24 -j MASQUERADE
