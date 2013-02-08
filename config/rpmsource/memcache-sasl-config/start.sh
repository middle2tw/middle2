#!/bin/sh

PORT=$1

memcached -S -p ${PORT} -d
