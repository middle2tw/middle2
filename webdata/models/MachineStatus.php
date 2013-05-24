<?php

class MachineStatus extends Pix_Table
{
    public function init()
    {
        $this->_name = 'machine_status';
        $this->_primary = array('name', 'updated_at');

        $this->_columns['name'] = array('type' => 'varchar', 'size' => 64);
        $this->_columns['status'] = array('type' => 'text');
        $this->_columns['updated_at'] = array('type' => 'int');
    }
}
