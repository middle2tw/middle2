<?php

class Addon_PgSQLDBRow extends Pix_Table_Row
{
    public function saveProjectVariable()
    {
        try {
            $this->project->variables->insert(array(
                'key' => 'DATABASE_URL',
                'value' => "pgsql://{$this->user_name}:{$this->password}@{$this->host}/{$this->database}",
                'is_magic_value' => 0,
            ));
        } catch (Pix_Table_DuplicateException $e) {
            $this->project->variables->search(array(
                'key' => 'DATABASE_URL',
            ))->update(array(
                'value' => "pgsql://{$this->user_name}:{$this->password}@{$this->host}/{$this->database}",
                'is_magic_value' => 0,
            ));
        }
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

        $host = getenv('PGSQL_USERDB_HOST');
        $user_name = Hisoku::uniqid(16);
        $password = Hisoku::uniqid(16);
        $database = 'user_' . $project->name;

        $db = new Pix_Table_Db_Adapter_PgSQL(array(
            'host' => getenv('PGSQL_USERDB_HOST'),
            'port' => getenv('PGSQL_USERDB_PORT'),
            'user' => getenv('PGSQL_USERDB_USER'),
            'password' => getenv('PGSQL_USERDB_PASS'),
        ));
        $db->query("CREATE USER \"{$user_name}\" WITH LOGIN PASSWORD '{$password}' NOINHERIT");
        $db->query("CREATE DATABASE \"{$database}\"");
        $db->query("GRANT ALL PRIVILEGES ON DATABASE \"{$database}\" TO \"{$user_name}\"");
        $db->query("REVOKE ALL PRIVILEGES ON DATABASE \"{$database}\" FROM PUBLIC");
        $db->query("ALTER DATABASE \"{$database}\" OWNER TO \"{$user_name}\"");
        $db->query("ALTER GROUP \"appdb\" ADD USER \"{$user_name}\"");

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
