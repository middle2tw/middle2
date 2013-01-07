<?php

class Admin extends Pix_Table
{
    public function init()
    {
        $this->_name = 'admin';
        $this->_primary = 'id';

        $this->_columns['id'] = array('type' => 'int');
    }
}
