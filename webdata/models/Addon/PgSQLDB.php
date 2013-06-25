<?php

class Addon_PgSQLDBRow extends Pix_Table_Row
{
    public function saveProjectVariable()
    {
        $this->project->variables->insert(array(
            'key' => 'DATABASE_URL',
            'value' => "pgsql://{$this->user_name}:{$this->password}@{$this->host}/{$this->database}",
        ));
    }
}

class Addon_PgSQLDB extends Pix_Table
{
    public function init()
    {
        $this->_name = 'addon_pgsqldb';
        $this->_rowClass = 'Addon_PgSQLDBRow';

        $this->_primary = array('id');

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true);
        $this->_columns['project_id'] = array('type' => 'int');
        $this->_columns['host'] = array('type' => 'varchar', 'size' => 255);
        $this->_columns['user_name'] = array('type' => 'varchar', 'size' => 32);
        $this->_columns['password'] = array('type' => 'varchar', 'size' => 32);
        $this->_columns['database'] = array('type' => 'varchar', 'size' => 32);

        $this->_relations['project'] = array('rel' => 'has_one', 'type' => 'Project', 'foreign_key' => 'project_id');

        $this->addIndex('project_id', array('project_id'));
    }

    public static function addDB($project)
    {
        if ($addon = self::search(array('project_id' => $project->id))->first()) {
            $addon->saveProjectVariable();
            return;
        }

        $host = USERDB_DOMAIN;
        $user_name = Hisoku::uniqid(16);
        $password = Hisoku::uniqid(16);
        $database = 'user_' . $project->name;

        $link = new mysqli(USERDB_DOMAIN, getenv('PGSQL_USERDB_USER'), getenv('PGSQL_USERDB_PASS'));
        $db = new Pix_Table_Db_Adapter_Pgsqli($link);
        $db->query("CREATE USER '{$user_name}'@'%' IDENTIFIED BY '{$password}'");
        $db->query("CREATE DATABASE IF NOT EXISTS`{$database}` CHARACTER SET utf8");
        $db->query("GRANT ALL PRIVILEGES ON  `{$database}` . * TO  '{$user_name}'@'%'");

        $addon = self::insert(array(
            'project_id' => $project->id,
            'host' => $host,
            'user_name' => $user_name,
            'password' => $password,
            'database' => $database,
        ));
        $addon->saveProjectVariable();

    }
}
