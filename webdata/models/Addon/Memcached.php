<?php

class Addon_MemcachedRow extends Pix_Table_Row
{
    public function saveProjectVariable()
    {
        $key_value = array(
            'MEMCACHE_SERVER' => $this->host,
            'MEMCACHE_PORT' => $this->port,
            'MEMCACHE_USERNAME' => $this->user_name,
            'MEMCACHE_PASSWORD' => $this->password,
        );
        foreach ($key_value as $key => $value) {
            try {
                $this->project->variables->insert(array(
                    'key' => $key,
                    'value' => $value,
                    'is_magic_value' => 0,
                ));
            } catch (Pix_Table_DuplicateException $e) {
                $this->project->variables->search(array(
                    'key' => $key,
                ))->update(array(
                    'value' => $value,
                    'is_magic_value' => 0,
                ));
            }
        }
    }
}

class Addon_Memcached extends Pix_Table
{
    public function init()
    {
        $this->_name = 'addon_memcache';
        $this->_rowClass = 'Addon_MemcachedRow';

        $this->_primary = array('id');

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true);
        $this->_columns['project_id'] = array('type' => 'int');
        $this->_columns['host'] = array('type' => 'varchar', 'size' => 255);
        $this->_columns['user_name'] = array('type' => 'varchar', 'size' => 32);
        $this->_columns['password'] = array('type' => 'varchar', 'size' => 32);
        $this->_columns['port'] = array('type' => 'int');

        $this->_relations['project'] = array('rel' => 'has_one', 'type' => 'Project', 'foreign_key' => 'project_id');

        $this->addIndex('project_id', array('project_id'));
    }

    public static function addDB($project)
    {
        $free_nodes_count = count(WebNode::search(array('project_id' => 0, 'status' => WebNode::STATUS_UNUSED)));
        if (!$free_nodes_count) {
            // TODO; log it
            throw new Exception('No free nodes');
        }

        if (!$random_node = WebNode::search(array('project_id' => 0, 'status' => WebNode::STATUS_UNUSED))->offset(rand(0, $free_nodes_count - 1))->first()) {
            throw new Exception('free node not found');
        }   

        $random_node->update(array(
            'project_id' => 0,
            'commit' => '',
            'start_at' => time(),
            'status' => WebNode::STATUS_SERVICE,
        ));

        $node_id = $random_node->port - 20000;
        $ip = long2ip($random_node->ip);


        $user_name = Hisoku::uniqid(16);
        $password = Hisoku::uniqid(16);

        $session = ssh2_connect($ip, 22);
        $ret = ssh2_auth_pubkey_file($session, 'root', WEB_PUBLIC_KEYFILE, WEB_KEYFILE);
        $stream = ssh2_exec($session, "service {$node_id} memcache-sasl {$user_name}+{$password}");
        stream_set_blocking($stream, true);
        $ret = stream_get_contents($stream);

        $addon = self::insert(array(
            'project_id' => $project->id,
            'host' => $ip,
            'user_name' => $user_name,
            'password' => $password,
            'port' => $random_node->port,
        ));
        $addon->saveProjectVariable();

    }
}
