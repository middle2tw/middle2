<?php

include(__DIR__ . '/../webdata/init.inc.php');

$m = new MemcacheSASL();
$m->addServer(getenv('MEMCACHE_SERVER'), getenv('MEMCACHE_PORT'));
$m->setSaslAuthData(getenv('MEMCACHE_USERNAME'), getenv('MEMCACHE_PASSWORD'));
$m->setSaveHandler();
session_start();

Pix_Controller::addCommonHelpers();
Pix_Controller::dispatch(__DIR__ . '/../webdata/');
