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
define('GIT_SERVER', 'git.hisoku.ronny.tw');
define('GIT_PRIVATE_SERVER', 'git-p.hisoku.ronny.tw');
define('USER_DOMAIN', '.hisokuapp.ronny.tw');
define('USERDB_DOMAIN', 'userdb.hisoku.ronny.tw');
define('WEB_KEYFILE', '/srv/config/web-key');
define('WEB_PUBLIC_KEYFILE', '/srv/config/web-key.pub');

Pix_Cache::addServer('Pix_Cache_Adapter_Memcache', array(
    'servers' => array(
        array('host' => 'memcache-p-1.hisoku.ronny.tw', 'port' => 11211, 'weight' => 1), // 256M
    ),
));

$db = new StdClass;
$db->host = getenv('MYSQL_HOST');
$db->username = getenv('MYSQL_USER');
$db->password = getenv('MYSQL_PASS');
$db->dbname = getenv('MYSQL_DATABASE');
$config = new StdClass;
$config->master = $config->slave = $db;
Pix_Table::setDefaultDb(new Pix_Table_Db_Adapter_MysqlConf(array($config)));
Pix_Table::setCache(new Pix_Cache);
