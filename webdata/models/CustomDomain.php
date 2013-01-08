<?php

class CustomDomain extends Pix_Table
{
    public function init()
    {
        $this->_name = 'custom_domain';
        $this->enableTableCache();

        $this->_primary = array('domain');

        // RFC 1035
        $this->_columns['domain'] = array('type' => 'varchar', 'size' => 255);
        $this->_columns['project_id'] = array('type' => 'int');

        $this->_relations['project'] = array('rel' => 'has_one', 'type' => 'Project', 'foreign_key' => 'project_id');
        $this->addIndex('project_id', array('project_id'));
    }
}
