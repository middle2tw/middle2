<?php

class UserRow extends Pix_Table_Row
{
}

class User extends Pix_Table
{
    public function init()
    {
        $this->_name = 'user';
        $this->_primary = 'id';
        $this->_rowClass = 'UserRow';

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true);
        $this->_columns['name'] = array('type' => 'varchar', 'size' => 32);
        $this->_columns['password_type'] = array('type' => 'tinyint');
        $this->_columns['password'] = array('type' => 'char', 'size' => 64);

        $this->_indexes['name'] = array('type' => 'unique', 'columns' => array('name'));
    }
}
