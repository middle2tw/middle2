<?php

class EAV extends Pix_Table
{
    public function init()
    {
        $this->_name = 'eav';
        $this->_primary = array('table', 'id', 'key');

        $this->_columns['table'] = array('type' => 'varchar', 'size' => 16);
        $this->_columns['id'] = array('type' => 'int');
        $this->_columns['key'] = array('type' => 'varchar', 'size' => 32);
        $this->_columns['value'] = array('type' => 'text');
    }
}
