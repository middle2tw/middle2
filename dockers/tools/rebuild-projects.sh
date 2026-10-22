#!/bin/sh
# Rebuild projects on the current middle2 base image, one at a time.
# Builds run on the load balancer host itself, so pause while its load is high.
# usage: rebuild-projects.sh <list-file> <result-file> <log-dir>
#   list-file: one project name per line
#   result-file: "<project>\t<ok|fail>\t<exit code>\t<seconds>\t<last log line>"
# Re-running skips projects already recorded in <result-file>.
MAX_LOAD=4
LIST=$1; RESULT=$2; LOGDIR=$3
mkdir -p $LOGDIR
for p in $(cat $LIST); do
    grep -q "^$p	" $RESULT 2>/dev/null && continue
    while [ $(awk '{print int($1)}' /proc/loadavg) -ge $MAX_LOAD ]; do
        echo "$(date +%T) load $(cut -d' ' -f1 /proc/loadavg), waiting" >> $LOGDIR/_wait.log
        sleep 30
    done
    START=$(date +%s)
    timeout 1800 sudo ssh -o BatchMode=yes git@middle2.com build-docker-project-image $p.git clean-build > $LOGDIR/$p.log 2>&1
    RC=$?
    if tail -1 $LOGDIR/$p.log | grep -q '^success:'; then ST=ok; else ST=fail; fi
    printf "%s\t%s\t%s\t%ss\t%s\n" "$p" "$ST" "$RC" "$(( $(date +%s) - START ))" "$(tail -1 $LOGDIR/$p.log | cut -c1-120)" >> $RESULT
done
echo "$(date +%T) DONE" >> $LOGDIR/_wait.log
