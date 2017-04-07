#!/bin/bash

outputs_dir=/srv/code/hisoku/firewall/outputs
dev='eth0'
ip_pattern='([0-9]+\.){3}[0-9]+'
docker_rules=()

function save_docker_rules() {
    rules=`iptables -S`
    while read -r line; do
        if echo "$line" | grep -iq docker; then
            docker_rules+=("$line")
        fi
    done <<< "$rules"
}

function restore_docker_rules() {
    for ((i = 0; i < ${#docker_rules[@]}; i++)); do
        iptables ${docker_rules[$i]}
    done
}

function run() {
    ip=`ip addr show dev "$dev" | grep -Eo "inet $ip_pattern" | grep -Eo "$ip_pattern" | grep -v '127.0.0.1'`
    script="$outputs_dir/$ip.sh"
    /bin/bash $script
}

function reload() {
    save_docker_rules
    run
    restore_docker_rules
}

case $1 in
    reload)
        reload
        ;;
    *)
        echo "Usage: $0 reload"
        ;;
esac
