<?php

if (!class_exists('Pix_Session')) {
    include(__DIR__ . '/../../../webdata/init.inc.php');

    Pix_Session::setAdapter('cookie', array('secret' => getenv('SESSION_SECRET'), 'cookie_key' => 'HISOKU_SESSION'));

    session_save_path('/tmp');

    if (!$user = Hisoku::getLoginUser()) {
        header('Location: /');
        exit;
    }
}

if (preg_match('/phpMyAdmin2/', $_SERVER['REQUEST_URI'])) { // 管理者模式
    // XXX: 這邊需要更嚴格安全的權限控管..或者之後要搬到內網
    if (!$user->isAdmin()) {
        header('Location: /');
        exit;
    }
    $addon_member = new StdClass;
    $addon_member->addon = new StdClass;
    $addon_member->addon->host = getenv('MYSQL_HOST');
    $addon_member->username = getenv('MYSQL_USER');
    $addon_member->password = getenv('MYSQL_PASS');
    $addon_member->addon->database = getenv('MYSQL_DATABASE');
    $addon_member->verbose = 'Main';

    $addon_members = array($addon_member);

    foreach (Hisoku::getMysqlServers() as $ip) {
        $addon_member = new StdClass;
        $addon_member->addon = new StdClass;
        $addon_member->addon->host = $ip;
        $addon_member->username = getenv('MYSQL_USERDB_USER');
        $addon_member->password = getenv('MYSQL_USERDB_PASS');
        $addon_member->verbose = 'UserDB';
        $addon_members[] = $addon_member;
    }

} else {
    $addon_members = Addon_MySQLDBMember::search(1)->searchIn('project_id', $user->project_members->toArraY('project_id'))->order('project_id');
    if (!count($addon_members)) {
        header('Location: /user/nodb');
        exit;
    }
}
$i = 0;
foreach ($addon_members as $addon_member) {
    $i++;
    /* Authentication type */
    $cfg['Servers'][$i]['auth_type'] = 'config';
    /* Server parameters */
    $cfg['Servers'][$i]['host'] = $addon_member->addon->host;
    $cfg['Servers'][$i]['user'] = $addon_member->username;
    if ($addon_member->project) {
        $cfg['Servers'][$i]['verbose'] = $addon_member->project->name . '(' . $addon_member->project->getEAV('note') . ')';
    } elseif ($addon_member->verbose) {
        $cfg['Servers'][$i]['verbose'] = $addon_member->verbose;
    }
    if ($addon_member->readonly) {
        $cfg['Servers'][$i]['verbose'] .= '(readonly)';
    }
    $cfg['Servers'][$i]['password'] = $addon_member->password;
    $cfg['Servers'][$i]['only_db'] = $addon_member->addon->database;
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
