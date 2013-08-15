<?php

class Addon_PgSQLDBRow extends Pix_Table_Row
{
    public function saveProjectVariable($key = 'DATABASE_URL')
    {
        Addon_PgSQLDBMember::search(array('addon_id' => $this->id, 'project_id' => $this->project_id))->first()->saveProjectVariable($key);
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
        return EAV::search(array('table' => 'AddonPgSQLDB', 'id' => $this->id));
    }

    public function addProject($project, $readonly = true)
    {
        $username = Hisoku::uniqid(16);
        $password = Hisoku::uniqid(16);

        $db = new Pix_Table_Db_Adapter_PgSQL(array(
            'host' => getenv('PGSQL_USERDB_HOST'),
            'port' => getenv('PGSQL_USERDB_PORT'),
            'user' => getenv('PGSQL_USERDB_USER'),
            'password' => getenv('PGSQL_USERDB_PASS'),
        ));

        try {
            $addon_member = Addon_PgSQLDBMember::insert(array(
                'project_id' => $project->id,
                'addon_id' => $this->id,
                'username' => $username,
                'password' => $password,
            ));
            $db->query("CREATE USER \"{$username}\" WITH LOGIN PASSWORD '{$password}' NOINHERIT");
            $db->query("ALTER GROUP \"appdb\" ADD USER \"{$username}\"");
        } catch (Pix_Table_DuplicateException $e) {
            $addon_member = Addon_PgSQLDBMember::find(array($this->id, $project->id));
            $db->query("REVOKE ALL PRIVILEGES ON DATABASE \"{$this->database}\" FROM \"{$addon_member->username}\"");
        }

        $addon_member->update(array(
            'readonly' => $readonly ? 1: 0,
        ));

        $db->query("GRANT ALL PRIVILEGES ON DATABASE \"{$this->database}\" TO \"{$addon_member->username}\"");
        // TODO: 需要研究 postgresql 怎麼對整個 db readonly
       /* if ($readonly) {
            $db->query("GRANT SELECT ON DATABASE \"{$this->database}\" TO \"{$addon_member->username}\"");
        } else {
        }*/
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
        $this->_relations['members'] = array('rel' => 'has_many', 'type' => 'Addon_PgSQLDBMember', 'foreign_key' => 'addon_id');

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

        $host = getenv('PGSQL_USERDB_HOST');
        $database = 'user_' . $project->name;

        $db = new Pix_Table_Db_Adapter_PgSQL(array(
            'host' => getenv('PGSQL_USERDB_HOST'),
            'port' => getenv('PGSQL_USERDB_PORT'),
            'user' => getenv('PGSQL_USERDB_USER'),
            'password' => getenv('PGSQL_USERDB_PASS'),
        ));
        $db->query("CREATE DATABASE \"{$database}\"");
        $db->query("REVOKE ALL PRIVILEGES ON DATABASE \"{$database}\" FROM PUBLIC");

        $addon = self::insert(array(
            'project_id' => $project->id,
            'host' => $host,
            'database' => $database,
        ));
        $addon->addProject($project, false);
        $addon->saveProjectVariable($key);
    }
}
