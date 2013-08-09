<?php

class Addon_MySQLDBMember extends Pix_Table
{
    public function init()
    {
        $this->_name = 'addon_mysqldb_member';
        $this->_primary = array('addon_id', 'project_id');

        $this->_columns['addon_id'] = array('type' => 'int');
        $this->_columns['project_id'] = array('type' => 'int');
        $this->_columns['username'] = array('type' => 'varchar', 'size' => 64);
        $this->_columns['password'] = array('type' => 'varchar', 'size' => 64);

        $this->addIndex('project_id', array('project_id'));
    }
}
