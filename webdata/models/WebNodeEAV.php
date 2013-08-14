<?php

class WebNodeEAV extends Pix_Table
{
    public function init()
    {
        $this->_name = 'webnode_eav';
        $this->_primary = array('ip', 'port', 'key');

        $this->_columns['ip'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['port'] = array('type' => 'int');
        $this->_columns['key'] = array('type' => 'varchar', 'size' => 32);
        $this->_columns['value'] = array('type' => 'text');
    }
}
