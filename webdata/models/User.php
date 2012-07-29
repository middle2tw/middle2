<?php

class UserRow extends Pix_Table_Row
{
    /**
     * addKey
     *
     * @param string $keybody
     * @access public
     * @throw InvalidArgumentException
     * @throw Pix_Table_DuplicateException
     * @return UserKeyRow
     */
    public function addKey($keybody)
    {
        $keybody = trim($keybody);
        $terms = explode(' ', $keybody);
        if (3 !== count($terms)) {
            throw new InvalidArgumentException('invalid key');
        }
        list($type, $body, $user) = $terms;
        if (!in_array($type, array('ssh-rsa', 'ssh-dsa'))) {
            throw new InvalidArgumentException('invalid ssh type');
        }

        if (preg_match('#[^a-zA-Z0-9/+=]#', $body)) {
            throw new InvalidArgumentException('invalid ssh key');
        }

        $key = $this->keys->insert(array(
            'key_fingerprint' => md5(base64_decode($body)),
            'key_body' => $keybody,
        ));

        return $key;

    }

    public function verifyPassword($password)
    {
        return $this->hashPassword($password) == $this->password;
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
            return base64_encode(hash_hmac('sha256', $password, $this->name, true));
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
        $this->_columns['name'] = array('type' => 'varchar', 'size' => 128);
        // password_type: 1-hmac_sha256
        $this->_columns['password_type'] = array('type' => 'tinyint');
        $this->_columns['password'] = array('type' => 'char', 'size' => 64);

        $this->_indexes['name'] = array('type' => 'unique', 'columns' => array('name'));

        $this->_relations['keys'] = array('rel' => 'has_many', 'type' => 'UserKey', 'foreign_key' => 'user_id', 'delete' => true);
        $this->_relations['project_members'] = array('rel' => 'has_many', 'type' => 'ProjectMember', 'foreign_key' => 'user_id');
    }
}
