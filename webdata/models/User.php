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
            throw new InvalidArgumentException('invalid key, there should be 3 terms in key');
        }
        list($type, $body, $user) = $terms;
        if (!in_array($type, array('ssh-rsa', 'ssh-dsa'))) {
            throw new InvalidArgumentException('invalid ssh type, first term must be ssh-rsa or ssh-dsa');
        }

        if (preg_match('#[^a-zA-Z0-9/+=]#', $body)) {
            throw new InvalidArgumentException('invalid ssh key, include invalid char');
        }

        $key = $this->keys->insert(array(
            'key_fingerprint' => md5(base64_decode($body)),
            'key_body' => $keybody,
        ));

        return $key;

    }

    public function addProject($name = '')
    {
        $name = trim($name);
        if ('' == $name) {
            $name = null;
        }

        if (!is_null($name)) {
            $name = strtolower($name);
            if (!preg_match('#^[a-z][a-z0-9-]+$#', $name)) {
                throw new InvalidArgumentException('invalid project name');
            }

            if (strlen($name) > 32 or strlen($name) < 6) {
                throw new InvalidArgumentException('project length in 6 ~ 32');
            }
        }

        $project = null;
        for ($i = 0; $i < 3; $i ++) {
            try {
                $project = Project::insert(array(
                    'name' => is_null($name) ? Project::getRandomName() : strval($name),
                    'created_at' => time(),
                    'created_by' => $this->id,
                ));
                break;
            } catch (Pix_Table_DuplicateException $e) {
                if (!is_null($name)) {
                    throw $e;
                }
            }
        }
        if (is_null($project)) {
            throw new Exception('generate name failed');
        }

        $project->members->insert(array(
            'user_id' => $this->id,
            'is_admin' => 1,
        ));

        return $project;
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

    public function isAdmin()
    {
        return Admin::find($this->id) ? true : false;
    }

    public function getOwnedMySQLDatabases()
    {
        $project_ids = $this->project_members->toArray('project_id');
        if (!$project_ids) {
            return array();
        }

        return Addon_MySQLDB::search(1)->searchIn('project_id', $project_ids);
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
        // 0 - created (wait confirm)
        // 1 - actived
        // 2 - disasbled
        $this->_columns['status'] = array('type' => 'int');

        $this->_indexes['name'] = array('type' => 'unique', 'columns' => array('name'));

        $this->_relations['keys'] = array('rel' => 'has_many', 'type' => 'UserKey', 'foreign_key' => 'user_id', 'delete' => true);
        $this->_relations['project_members'] = array('rel' => 'has_many', 'type' => 'ProjectMember', 'foreign_key' => 'user_id');
    }
}
