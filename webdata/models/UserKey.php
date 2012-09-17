<?php

class UserKeyRow extends Pix_Table_Row
{
    public function postSave()
    {
        $this->_updateGitKey();
    }

    public function postDelete()
    {
        $this->_updateGitKey();
    }

    protected function _updateGitKey()
    {
        $ip = GIT_SERVER;
        $session = ssh2_connect($ip, 22);
        ssh2_auth_pubkey_file($session, 'git', '/srv/config/web-key.pub', '/srv/config/web-key');
        ssh2_exec($session, "update-keys");
    }

    public function getKeyUser()
    {
        list($type, $body, $user) = explode(' ', $this->key_body);
        return $user;
    }
}

class UserKey extends Pix_Table
{
    public function init()
    {
        $this->_name = 'user_key';
        $this->_primary = array('id');
        $this->_rowClass = 'UserKeyRow';

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true);
        $this->_columns['user_id'] = array('type' => 'int');
        // ex: 23:47:08:1d:57:09:1b:f5:df:fe:96:52:0d:1d:57:5d
        $this->_columns['key_fingerprint'] = array('type' => 'char', 'size' => 32);
        $this->_columns['key_body'] = array('type' => 'text');

        $this->_indexes['userid_id'] = array('type' => 'unique', 'columns' => array('user_id', 'id'));
        $this->_indexes['key_fingerprint'] = array('type' => 'unique', 'columns' => array('key_fingerprint'));

        $this->_relations['user'] = array('rel' => 'has_one', 'type' => 'User', 'foreign_key' => 'user_id');
    }
}
