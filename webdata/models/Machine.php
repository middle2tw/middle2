<?php

class MachineRow extends Pix_Table_Row
{
    public function getGroups()
    {
        return explode(',', $this->groups);
    }

    public function setGroups($groups)
    {
        $this->groups = $groups;
        $this->save();
    }
}

class Machine extends Pix_Table
{
    public function init()
    {
        $this->_name = 'machine';
        $this->_primary = 'machine_id';
        $this->_rowClass = 'MachineRow';

        $this->_columns['machine_id'] = array('type' => 'int', 'auto_increment' => true);
        $this->_columns['ip'] = array('type' => 'int', 'unsigned' => true);
        // TODO: 之後搬出另一個 model
        $this->_columns['groups'] = array('type' => 'text');
    }
}
