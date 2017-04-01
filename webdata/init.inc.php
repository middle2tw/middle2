<?php

error_reporting(E_ALL ^ E_STRICT ^ E_NOTICE);

include(__DIR__ . '/stdlibs/pixframework/Pix/Loader.php');
set_include_path(__DIR__ . '/stdlibs/pixframework/'
    . PATH_SEPARATOR . __DIR__ . '/models'
);

Pix_Loader::registerAutoLoad();

if (file_exists(__DIR__ . '/config.php')) {
    include(__DIR__ . '/config.php');
} elseif (file_exists('/srv/config/config.php')) {
    include('/srv/config/config.php');
}
define('GIT_SERVER', getenv('GIT_SERVER') ?: 'git.middle2.com');
define('USER_DOMAIN', getenv('APP_SUFFIX') ?: '.hisokuapp.ronny.tw');
define('WEB_KEYFILE', '/srv/config/web-key');
define('WEB_PUBLIC_KEYFILE', '/srv/config/web-key.pub');
define('MEMCACHE_PRIVATE_HOST', getenv('MEMCACHE_PRIVATE_HOST'));
define('MEMCACHE_PRIVATE_PORT', getenv('MEMCACHE_PRIVATE_PORT'));

// TODO: 之後要搭配 geoip
date_default_timezone_set('Asia/Taipei');

$db = new StdClass;
$db->host = getenv('MYSQL_HOST');
$db->username = getenv('MYSQL_USER');
$db->password = getenv('MYSQL_PASS');
$db->dbname = getenv('MYSQL_DATABASE');
$config = new StdClass;
$config->master = $config->slave = $db;
Pix_Table::setDefaultDb(new Pix_Table_Db_Adapter_MysqlConf(array($config)));
if (MEMCACHE_PRIVATE_HOST) {
    Pix_Cache::addServer('Pix_Cache_Adapter_Memcached', array(
        'servers' => array(
            array('host' => MEMCACHE_PRIVATE_HOST, 'port' => MEMCACHE_PRIVATE_PORT, 'weight' => 1), // 256M
        ),
    ));
    Pix_Table::setCache(new Pix_Cache);
}

