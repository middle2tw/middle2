<?php

class ProjectVariable extends Pix_Table
{
    public function init()
    {
        $this->_name = 'project_variable';
        $this->_primary = array('project_id', 'key');

        $this->_columns['project_id'] = array('type' => 'int');
        $this->_columns['key'] = array('type' => 'varchar', 'size' => 32);
        $this->_columns['value'] = array('type' => 'text');

        $this->_relations['project'] = array('rel' => 'has_one', 'type' => 'Project', 'foreign_key' => 'project_id');
    }
}
