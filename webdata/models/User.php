<?php

class UserRow extends Pix_Table_Row
{
    public function verifyPassword($password)
    {
        return $this->hashPassword($psasword) == $this->password;
    }

    public function setPassword($password)
    {
        $this->password_type = 1;
        $this->password = $this->hashPassword($password);
        $this->save();
    }

    public function hashPassword($password)
    {
        switch ($this->password_type) {
        case 1:
            return hash_hmac('sha256', $password, $this->name, true);
        default:
            throw new Excpetion('unknown password_type');
        }
    }
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
        // password_type: 1-hmac_sha256
        $this->_columns['password_type'] = array('type' => 'tinyint');
        $this->_columns['password'] = array('type' => 'char', 'size' => 64);

        $this->_indexes['name'] = array('type' => 'unique', 'columns' => array('name'));
    }
}
