<?php

class Addon_MySQLDB extends Pix_Table
{
    public function init()
    {
        $this->_name = 'addon_mysqldb';

        $this->_primary = array('id');

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true);
        $this->_columns['project_id'] = array('type' => 'int');
        $this->_columns['host'] = array('type' => 'varchar', 'size' => 255);
        $this->_columns['user_name'] = array('type' => 'varchar', 'size' => 32);
        $this->_columns['password'] = array('type' => 'varchar', 'size' => 32);
        $this->_columns['database'] = array('type' => 'varchar', 'size' => 32);

        $this->addIndex('project_id', array('project_id'));
    }

    public static function addDB($project)
    {
        if (self::search(array('project_id' => $project->id))->count()) {
            return;
        }

        $host = USERDB_DOMAIN;
        $user_name = Hisoku::uniqid(16);
        $password = Hisoku::uniqid(16);
        $database = 'user_' . $project->name;

        $link = new mysqli(USERDB_DOMAIN, getenv('MYSQL_USERDB_USER'), getenv('MYSQL_USERDB_PASS'));
        $db = new Pix_Table_Db_Adapter_Mysqli($link);
        $db->query("CREATE USER '{$user_name}'@'%' IDENTIFIED BY '{$password}'");
        $db->query("CREATE DATABASE IF NOT EXISTS`{$database}`");
        $db->query("GRANT ALL PRIVILEGES ON  `{$database}` . * TO  '{$user_name}'@'%'");

        self::insert(array(
            'project_id' => $project->id,
            'host' => $host,
            'user_name' => $user_name,
            'password' => $password,
            'database' => $database,
        ));

        $project->variables->insert(array(
            'key' => 'DATABASE_URL',
            'value' => "mysql://{$user_name}:{$password}@{$host}/{$database}",
        ));
    }
}
