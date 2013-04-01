<?php

include(__DIR__ . '/../../../webdata/init.inc.php');

Pix_Session::setAdapter('cookie', array('secret' => getenv('SESSION_SECRET'), 'cookie_key' => 'HISOKU_SESSION'));

$m = new MemcacheSASL();
$m->addServer(getenv('MEMCACHE_SERVER'), getenv('MEMCACHE_PORT'));
$m->setSaslAuthData(getenv('MEMCACHE_USERNAME'), getenv('MEMCACHE_PASSWORD'));
$m->setSaveHandler();
session_start();

if (!$user = Hisoku::getLoginUser()) {
    header('Location: /');
    exit;
}

$addons = Addon_MySQLDB::search(1)->searchIn('project_id', $user->project_members->toArraY('project_id'));
$i = 0;
foreach ($addons as $addon) {
    $i++;
    /* Authentication type */
    $cfg['Servers'][$i]['auth_type'] = 'config';
    /* Server parameters */
    $cfg['Servers'][$i]['host'] = $addon->host;
    $cfg['Servers'][$i]['user'] = $addon->user_name;
    $cfg['Servers'][$i]['verbose'] = $addon->project->name . '(' . $addon->project->getEAV('note') . ')';
    $cfg['Servers'][$i]['password'] = $addon->password;
    $cfg['Servers'][$i]['only_db'] = $addon->database;
    $cfg['Servers'][$i]['connect_type'] = 'tcp';
    $cfg['Servers'][$i]['compress'] = false;
    /* Select mysql if your server does not have mysqli */
    $cfg['Servers'][$i]['extension'] = 'mysqli';
    $cfg['Servers'][$i]['AllowNoPassword'] = false;
}

$cfg['UploadDir'] = '';
$cfg['SaveDir'] = '';

/**
 * Defines whether a user should be displayed a "show all (records)"
 * button in browse mode or not.
 * default = false
 */
//$cfg['ShowAll'] = true;

/**
 * Number of rows displayed when browsing a result set. If the result
 * set contains more rows, "Previous" and "Next".
 * default = 30
 */
//$cfg['MaxRows'] = 50;

/**
 * Use graphically less intense menu tabs
 * default = false
 */
//$cfg['LightTabs'] = true;

/**
 * disallow editing of binary fields
 * valid values are:
 *   false  allow editing
 *   'blob' allow editing except for BLOB fields
 *   'all'  disallow editing
 * default = blob
 */
//$cfg['ProtectBinary'] = 'false';

/**
 * Default language to use, if not browser-defined or user-defined
 * (you find all languages in the locale folder)
 * uncomment the desired line:
 * default = 'en'
 */
//$cfg['DefaultLang'] = 'en';
//$cfg['DefaultLang'] = 'de';

/**
 * default display direction (horizontal|vertical|horizontalflipped)
 */
//$cfg['DefaultDisplay'] = 'vertical';


/**
 * How many columns should be used for table display of a database?
 * (a value larger than 1 results in some information being hidden)
 * default = 1
 */
//$cfg['PropertiesNumColumns'] = 2;

/**
 * Set to true if you want DB-based query history.If false, this utilizes
 * JS-routines to display query history (lost by window close)
 *
 * This requires configuration storage enabled, see above.
 * default = false
 */
//$cfg['QueryHistoryDB'] = true;

/**
 * When using DB-based query history, how many entries should be kept?
 *
 * default = 25
 */
//$cfg['QueryHistoryMax'] = 100;

/*
 * You can find more configuration options in Documentation.html
 * or here: http://wiki.phpmyadmin.net/pma/Config
 */
?>
