<?php

class Machine extends Pix_Table
{
    public function init()
    {
        $this->_name = 'machine';
        $this->_primary = 'machine_id';

        $this->_columns['machine_id'] = array('type' => 'int', 'auto_increment' => true);
        $this->_columns['ip'] = array('type' => 'int', 'unsigned' => true);
        // TODO: 之後搬出另一個 model
        $this->_columns['groups'] = array('type' => 'text');
    }
}
