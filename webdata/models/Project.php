<?php

class Project extends Pix_Table
{
    public function init()
    {
        $this->_name = 'project';
        $this->_primary = 'id';

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true);
        $this->_columns['name'] = array('type' => 'varchar', 'size' => 64);
        $this->_columns['created_at'] = array('type' => 'int');
        $this->_columns['created_by'] = array('type' => 'int');

        $this->_indexes['name'] = array('type' => 'unique', 'columns' => array('name'));
    }
}
