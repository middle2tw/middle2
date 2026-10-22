#!/bin/sh
# Print "<project>\t<image created unix time>" for every project rebuilt OK,
# as input for release-old-nodes.php.
# usage: image-times.sh <result-file from rebuild-projects.sh> > image-times.tsv
REGISTRY=docker-registry-linode-1.middle2.com:5000

awk -F'\t' '$2=="ok"{print $1}' $1 | while read p; do
    c=$(docker image inspect --format '{{.Created}}' $REGISTRY/image-$p 2>/dev/null) || continue
    printf "%s\t%s\n" "$p" "$(date -d "$c" +%s)"
done
