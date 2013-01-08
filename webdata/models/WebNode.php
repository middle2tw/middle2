<?php

class WebNode extends Pix_Table
{
    const STATUS_UNUSED = 0;
    const STATUS_WEBPROCESSING = 1;
    const STATUS_CRONPROCESSING = 2;
    const STATUS_WEBNODE = 10;
    const STATUS_CRONNODE = 11;

    public function init()
    {
        $this->_name = 'webnode';
        $this->_primary = array('ip', 'port');

        $this->_columns['ip'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['port'] = array('type' => 'int');
        $this->_columns['project_id'] = array('type' => 'int');
        $this->_columns['commit'] = array('type' => 'char', 'size' => 32);
        // status: 0-unused,
        //         1-webprocessing, 2-cronprocessing
        //         10-webnode, 11-cronnode
        $this->_columns['status'] = array('type' => 'tinyint');
        $this->_columns['created_at'] = array('type' => 'int');
        $this->_columns['start_at'] = array('type' => 'int');
        $this->_columns['access_at'] = array('type' => 'int');

        $this->_relations['project'] = array('rel' => 'has_one', 'type' => 'Project', 'foreign_key' => 'project_id');

        $this->addIndex('projectid_status_commit', array(
            'project_id',
            'status',
            'commit',
        ));
    }

    public static function getUnusedNode($project = null)
    {
        $free_nodes_count = count(WebNode::search(array('project_id' => 0)));
        if (!$free_nodes_count) {
            // TODO; log it
            throw new Exception('no free nods');
        }

        if (!$random_node = WebNode::search(array('project_id' => 0))->offset(rand(0, $free_nodes_count - 1))->first()) {
            // TODO: log it
            throw new Exception('no free nods');
        }

        $random_node->update(array(
            'project_id' => $project->id,
            'commit' => $project->commit,
            'status' => WebNode::STATUS_WEBPROCESSING,
        ));

        $node_id = $random_node->port - 20000;
        $ip = long2ip($random_node->ip);

        $session = ssh2_connect($ip, 22);
        ssh2_auth_pubkey_file($session, 'root', WEB_PUBLIC_KEYFILE, WEB_KEYFILE);
        ssh2_exec($session, "clone {$project->name} {$node_id}");

        return $random_node;
    }

    public static function getGroupedNodes()
    {
        $return = array();

        foreach (WebNode::search(1)->order(array('ip', 'port')) as $node) {
            $return[$node->ip][] = $node;
        }

        return $return;
    }

    public static function initNode($ip, $port)
    {
        $session = ssh2_connect($ip, 22);
        ssh2_auth_pubkey_file($session, 'root', WEB_PUBLIC_KEYFILE, WEB_KEYFILE);
        $stream = ssh2_exec($session, "init $port");
    }
}
