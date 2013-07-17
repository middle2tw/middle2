<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
if (!class_exists('Pix_Session')) {
    include(__DIR__ . '/../../../webdata/init.inc.php');

    Pix_Session::setAdapter('cookie', array('secret' => getenv('SESSION_SECRET'), 'cookie_key' => 'HISOKU_SESSION'));

    session_save_path('/tmp');

    if (!$user = Hisoku::getLoginUser()) {
        header('Location: /');
        exit;
    }
}

$addons = Addon_PgSQLDB::search(1)->searchIn('project_id', $user->project_members->toArraY('project_id'));
if (!count($addons)) {
    header('Location: /user/nodb');
    exit;
}
$i = 0;
$conf['servers'] = array();
foreach ($addons as $addon) {
    $conf['servers'][$i] = array();
    if ($addon->project) {
        $conf['servers'][$i]['desc'] = $addon->project->name . '(' . $addon->project->getEAV('note') . ')';
    } elseif ($addon->verbose) {
        $conf['servers'][$i]['desc'] = $addon->verbose;
    }
    $conf['servers'][$i]['host'] = $addon->host;
    $conf['servers'][$i]['port'] = 5432;
    $conf['servers'][$i]['username'] = $addon->user_name;
    $conf['servers'][$i]['password'] = $addon->password;
    $conf['servers'][$i]['sslmode'] = 'allow';
    $conf['servers'][$i]['defaultdb'] = $addon->database;
    $conf['servers'][$i]['pg_dump_path'] = '/usr/bin/pg_dump';
    $conf['servers'][$i]['pg_dumpall_path'] = '/usr/bin/pg_dumpall';
    $i ++;
}




	$conf['default_lang'] = 'auto';

	// AutoComplete uses AJAX interaction to list foreign key values 
	// on insert fields. It currently only works on single column 
	// foreign keys. You can choose one of the following values:
	// 'default on' enables AutoComplete and turns it on by default.
	// 'default off' enables AutoComplete but turns it off by default.
	// 'disable' disables AutoComplete.
	$conf['autocomplete'] = 'default on';
	
	// If extra login security is true, then logins via phpPgAdmin with no
	// password or certain usernames (pgsql, postgres, root, administrator)
	// will be denied. Only set this false once you have read the FAQ and
	// understand how to change PostgreSQL's pg_hba.conf to enable
	// passworded local connections.
	$conf['extra_login_security'] = true;

	// Only show owned databases?
	// Note: This will simply hide other databases in the list - this does
	// not in any way prevent your users from seeing other database by
	// other means. (e.g. Run 'SELECT * FROM pg_database' in the SQL area.)
	$conf['owned_only'] = true;

	// Display comments on objects?  Comments are a good way of documenting
	// a database, but they do take up space in the interface.
	$conf['show_comments'] = true;

	// Display "advanced" objects? Setting this to true will show 
	// aggregates, types, operators, operator classes, conversions, 
	// languages and casts in phpPgAdmin. These objects are rarely 
	// administered and can clutter the interface.
	$conf['show_advanced'] = false;

	// Display "system" objects?
	$conf['show_system'] = false;

	// Minimum length users can set their password to.
	$conf['min_password_length'] = 1;

	// Width of the left frame in pixels (object browser)
	$conf['left_width'] = 200;
	
	// Which look & feel theme to use
	$conf['theme'] = 'default';
	
	// Show OIDs when browsing tables?
	$conf['show_oids'] = false;
	
	// Max rows to show on a page when browsing record sets
	$conf['max_rows'] = 30;

	// Max chars of each field to display by default in browse mode
	$conf['max_chars'] = 50;

	// Send XHTML strict headers?
	$conf['use_xhtml_strict'] = false;

	// Base URL for PostgreSQL documentation.
	// '%s', if present, will be replaced with the PostgreSQL version
	// (e.g. 8.4 )
	$conf['help_base'] = 'http://www.postgresql.org/docs/%s/interactive/';
	
	// Configuration for ajax scripts
	// Time in seconds. If set to 0, refreshing data using ajax will be disabled (locks and activity pages)
	$conf['ajax_refresh'] = 3;

	/** Plugins management
	 * Add plugin names to the following array to activate them
	 * Example:
	 *   $conf['plugins'] = array(
	 *     'Example',
	 *     'Slony'
	 *   );
	 */
	$conf['plugins'] = array();

	/*****************************************
	 * Don't modify anything below this line *
	 *****************************************/

	$conf['version'] = 19;

?>
