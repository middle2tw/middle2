<?php

class WebNode extends Pix_Table
{
    public function init()
    {
        $this->_name = 'webnode';
        $this->_primary = array('ip', 'port');

        $this->_columns['ip'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['port'] = array('type' => 'int');
        $this->_columns['project_id'] = array('type' => 'int');
        $this->_columns['commit'] = array('type' => 'char', 'size' => 32);
        $this->_columns['created_at'] = array('type' => 'int');
        $this->_columns['start_at'] = array('type' => 'int');
        $this->_columns['access_at'] = array('type' => 'int');

        $this->_indexes['projectid_commit'] = array('project_id', 'commit');
    }
}
