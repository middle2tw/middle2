<?php

class MachineStatus extends Pix_Table
{
    public function init()
    {
        $this->_name = 'machine_status';
        $this->_primary = array('machine_id', 'updated_at');

        $this->_columns['machine_id'] = array('type' => 'int');
        $this->_columns['status'] = array('type' => 'text');
        $this->_columns['updated_at'] = array('type' => 'int');
    }
}
