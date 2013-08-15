<?php

class Addon_PgSQLDBMemberRow extends Pix_Table_Row
{
    public function getDatabaseURL()
    {
        return "pgsql://{$this->username}:{$this->password}@{$this->addon->host}/{$this->addon->database}";
    }

    public function saveProjectVariable($key = 'DATABASE_URL')
    {
        try {
            $this->project->variables->insert(array(
                'key' => $key,
                'value' => "Addon_PgSQLDB:{$this->addon_id}:DatabaseURL",
                'is_magic_value' => 1,
            ));
        } catch (Pix_Table_DuplicateException $e) {
            $this->project->variables->search(array(
                'key' => $key,
            ))->update(array(
                'value' => "Addon_PgSQLDB:{$this->addon_id}:DatabaseURL",
                'is_magic_value' => 1,
            ));
        }
    }
}

class Addon_PgSQLDBMember extends Pix_Table
{
    public function init()
    {
        $this->_name = 'addon_pgsqldb_member';
        $this->_primary = array('addon_id', 'project_id');
        $this->_rowClass = 'Addon_PgSQLDBMemberRow';

        $this->_columns['addon_id'] = array('type' => 'int');
        $this->_columns['project_id'] = array('type' => 'int');
        $this->_columns['username'] = array('type' => 'varchar', 'size' => 64);
        $this->_columns['password'] = array('type' => 'varchar', 'size' => 64);
        $this->_columns['readonly'] = array('type' => 'int', 'default' => 0);

        $this->addIndex('project_id', array('project_id'));

        $this->_relations['addon'] = array('rel' => 'has_one', 'type' => 'Addon_PgSQLDB', 'foreign_key' => 'addon_id');
        $this->_relations['project'] = array('rel' => 'has_one', 'type' => 'Project', 'foreign_key' => 'project_id');
    }
}
