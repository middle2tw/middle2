<?php

class MachineStatusRow extends Pix_Table_Row
{
    protected $_obj = null;

    public function getObject()
    {
        if (is_null($this->_obj)) {
            $this->_obj = json_decode($this->status);
        }
        return $this->_obj;
    }

    public function getLoads()
    {
        $obj = $this->getObject();
        if (!preg_match('#,  load average: ([0-9.]*), ([0-9.]*), ([0-9.]*)#', $obj->process, $matches)) {
            throw new Exception("process info not found");
        }

        return array(floatval($matches[1]), floatval($matches[2]), floatval($matches[3]));
    }
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
