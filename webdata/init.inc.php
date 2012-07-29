<?php

include(__DIR__ . '/stdlibs/pixframework/Pix/Loader.php');
set_include_path(__DIR__ . '/stdlibs/pixframework/'
    . PATH_SEPARATOR . __DIR__ . '/models'
);

Pix_Loader::registerAutoLoad();

if (file_exists(__DIR__ . '/config.php')) {
    include(__DIR__ . '/config.php');
}
$link = new mysqli;
$link->connect(getenv('MYSQL_HOST'), getenv('MYSQL_USER'), getenv('MYSQL_PASS'));
$link->select_db(getenv('MYSQL_DATABASE'));
Pix_Table::setDefaultDb(new Pix_Table_Db_Adapter_Mysqli($link));
