<?php

class Addon_MySQLDBRow extends Pix_Table_Row
{
    public function saveProjectVariable($key = 'DATABASE_URL')
    {
        Addon_MySQLDBMember::search(array('addon_id' => $this->id, 'project_id' => $this->project_id))->first()->saveProjectVariable($key);
    }

    public function isMember($user)
    {
        return $this->project->isMember($user);
    }

    public function isAdmin($user)
    {
        return $this->project->isAdmin($user);
    }

    public function getEAVs()
    {
        return EAV::search(array('table' => 'AddonMySQLDB', 'id' => $this->id));
    }
}

class Addon_MySQLDB extends Pix_Table
{
    public function init()
    {
        $this->_name = 'addon_mysqldb';
        $this->_rowClass = 'Addon_MySQLDBRow';

        $this->_primary = array('id');

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true);
        $this->_columns['project_id'] = array('type' => 'int');
        $this->_columns['host'] = array('type' => 'varchar', 'size' => 255);
        $this->_columns['database'] = array('type' => 'varchar', 'size' => 32);

        $this->_relations['project'] = array('rel' => 'has_one', 'type' => 'Project', 'foreign_key' => 'project_id');
        $this->_relations['members'] = array('rel' => 'has_many', 'type' => 'Addon_MySQLDBMember', 'foreign_key' => 'addon_id');

        $this->addIndex('project_id', array('project_id'));

        $this->_hooks['eavs'] = array('get' => 'getEAVs');

        $this->addRowHelper('Pix_Table_Helper_EAV', array('getEAV', 'setEAV'));
    }

    public static function addDB($project, $key = 'DATABASE_URL')
    {
        if ($addon = self::search(array('project_id' => $project->id))->first()) {
            $addon->saveProjectVariable($key);
            return;
        }

        $ips = Hisoku::getMysqlServers();
        $host = $ips[0];
        $username = Hisoku::uniqid(16);
        $password = Hisoku::uniqid(16);
        $database = 'user_' . $project->name;

        $link = new mysqli($ips[0], getenv('MYSQL_USERDB_USER'), getenv('MYSQL_USERDB_PASS'));
        $db = new Pix_Table_Db_Adapter_Mysqli($link);
        $db->query("CREATE USER '{$username}'@'%' IDENTIFIED BY '{$password}'");
        $db->query("CREATE DATABASE IF NOT EXISTS`{$database}` CHARACTER SET utf8");
        $db->query("GRANT ALL PRIVILEGES ON  `{$database}` . * TO  '{$username}'@'%'");

        $addon = self::insert(array(
            'project_id' => $project->id,
            'host' => $host,
            'database' => $database,
        ));

        Addon_MySQLDBMember::insert(array(
            'project_id' => $project->id,
            'addon_id' => $addon->id,
            'username' => $username,
            'password' => $password,
        ));

        $addon->saveProjectVariable($key);
    }
}
