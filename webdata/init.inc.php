<?php

error_reporting(E_ALL ^ E_STRICT ^ E_NOTICE);

include(__DIR__ . '/stdlibs/pixframework/Pix/Loader.php');
set_include_path(__DIR__ . '/stdlibs/pixframework/'
    . PATH_SEPARATOR . __DIR__ . '/models'
);

Pix_Loader::registerAutoLoad();

if (file_exists('/srv/config/config.php')) {
    include('/srv/config/config.php');
} elseif (file_exists(__DIR__ . '/config.php')) {
    include(__DIR__ . '/config.php');
}
define('GIT_SERVER', 'git.hisoku.ronny.tw');
define('GIT_PRIVATE_SERVER', 'git-p.hisoku.ronny.tw');
define('USER_DOMAIN', '.hisokuapp.ronny.tw');
define('USERDB_DOMAIN', 'userdb.hisoku.ronny.tw');
define('WEB_KEYFILE', '/srv/config/web-key');
define('WEB_PUBLIC_KEYFILE', '/srv/config/web-key.pub');

$link = new mysqli;
$link->connect(getenv('MYSQL_HOST'), getenv('MYSQL_USER'), getenv('MYSQL_PASS'));
$link->select_db(getenv('MYSQL_DATABASE'));
Pix_Table::setDefaultDb(new Pix_Table_Db_Adapter_Mysqli($link));
