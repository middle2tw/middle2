#!/usr/bin/env php
<?php

define('LOCK_FILE', '/tmp/hisoku-updatenodes');
$fp = fopen(LOCK_FILE, 'w+');
if (!flock($fp, LOCK_EX | LOCK_NB)) {
    throw new Exception(LOCK_FILE . ' is locking');
}

ftruncate($fp, 0);      // truncate file
fwrite($fp, getmypid());
fflush($fp);            // flush output before releasing the lock

include(__DIR__ . '/../../webdata/init.inc.php');
WebNode::updateNodeInfo();

flock($fp, LOCK_UN);    // release the lock
fclose($fp);
unlink(LOCK_FILE);
