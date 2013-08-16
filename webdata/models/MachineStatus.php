<?php

class MachineStatusRow extends Pix_Table_Row
{
}

class MachineStatus extends Pix_Table
{
    public function init()
    {
        $this->_name = 'machine_status';
        $this->_primary = array('machine_id', 'updated_at');
        $this->_rowClass = 'MachineStatusRow';

        $this->_columns['machine_id'] = array('type' => 'int');
        $this->_columns['status'] = array('type' => 'text');
        $this->_columns['updated_at'] = array('type' => 'int');

        $this->_relations['machine'] = array('rel' => 'has_one', 'type' => 'Machine');
    }
}
