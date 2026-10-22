#!/bin/sh
# List project images on this machine (the load balancer host, where builds run) with how each one runs, as TSV:
#   <project>\t<type>\t<image created date>
# type: procfile / django / python / ruby / laravel / php-fpm (same order as start-web.sh)
# usage: list-projects.sh > projects.tsv
REGISTRY=docker-registry-linode-1.middle2.com:5000

for p in $(docker images --format '{{.Repository}}' | grep "^$REGISTRY/image-" | sed 's#.*/image-##' | sort -u); do
    t=$(docker run --rm --entrypoint sh $REGISTRY/image-$p -c '
        cd /srv/web 2>/dev/null || { echo noweb; exit; }
        if [ -f Procfile ]; then echo procfile
        elif [ -f manage.py ]; then echo django
        elif [ -f app.py ]; then echo python
        elif [ -f web.rb ]; then echo ruby
        elif [ -f artisan ]; then echo laravel
        else echo php-fpm
        fi' 2>/dev/null)
    c=$(docker image inspect --format '{{.Created}}' $REGISTRY/image-$p | cut -c1-10)
    printf "%s\t%s\t%s\n" "$p" "${t:-error}" "$c"
done
