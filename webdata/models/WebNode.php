<?php

class WebNodeRow extends Pix_Table_Row
{
    public function markAsUnused()
    {
        $this->update(array(
            'project_id' => 0,
            'commit' => '',
            'status' => WebNode::STATUS_OVER,
        ));
    }

    protected function _sshDeletePort()
    {
        $session = ssh2_connect(long2ip($this->ip), 22);
        if (false === $session) {
            return false;
        }
        $ret = ssh2_auth_pubkey_file($session, 'root', WEB_PUBLIC_KEYFILE, WEB_KEYFILE);
        if (false === $session) {
            return false;
        }
        $stream = ssh2_exec($session, "shutdown " . ($this->port - 20000));
        stream_set_blocking($stream, true);
        $ret = stream_get_contents($stream);
        if (!$ret = json_decode($ret)) {
            return false;
        }
        if ($ret->error) {
            return false;
        }
        return true;
    }

    public function preInsert()
    {
        $this->created_at = time();
    }

    public function postDelete()
    {
        $this->_sshDeletePort();
    }

    /**
     * getAccessAt 取得 access at ，如果 cache 有就取比較新的時間
     *
     * @return int timestamp
     */
    public function getAccessAt()
    {
        $c = new Pix_Cache;
        return max($this->access_at, intval($c->get("WebNode:access_at:{$this->ip}:{$this->port}")));
    }

    /**
     * getNodeProcesses get the process list on node
     * 
     * @access public
     * @return array
     */
    public function getNodeProcesses()
    {
        $session = ssh2_connect(long2ip($this->ip), 22);
        if (false === $session) {
            return false;
        }
        $ret = ssh2_auth_pubkey_file($session, 'root', WEB_PUBLIC_KEYFILE, WEB_KEYFILE);
        if (false === $session) {
            return false;
        }
        $stream = ssh2_exec($session, "check_alive " . ($this->port - 20000));
        stream_set_blocking($stream, true);
        $ret = stream_get_contents($stream);
        $ret = json_decode($ret);
        if (!is_array($ret)) {
            return false;
        }
        if ($ret->error) {
            return false;
        }
        return $ret;
    }

    public function runJob($command)
    {
        $session = ssh2_connect(long2ip($this->ip), 22);
        if (false === $session) {
            throw new Exception('connect failed');
        }
        $ret = ssh2_auth_pubkey_file($session, 'root', WEB_PUBLIC_KEYFILE, WEB_KEYFILE);
        if (false === $ret) {
            throw new Exception('ssh key is wrong');
        }
        $node_id = $this->port - 20000;
        $stream = ssh2_exec($session, "run {$this->project->name} {$node_id} " . urlencode($command));
        $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
        $ret = new StdClass;
        $ret->stdout = $stream;
        $ret->stdio = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
        $ret->stderr = $errorStream;
        return $ret;
    }
}

class WebNode extends Pix_Table
{
    const STATUS_UNUSED = 0;
    const STATUS_WEBPROCESSING = 1;
    const STATUS_CRONPROCESSING = 2;
    const STATUS_WEBNODE = 10;
    const STATUS_CRONNODE = 11;
    const STATUS_STOP = 100;
    const STATUS_OVER = 101; // 等待資源再被放出來

    public function init()
    {
        $this->_name = 'webnode';
        $this->_primary = array('ip', 'port');
        $this->_rowClass = 'WebNodeRow';
        $this->enableTableCache();

        $this->_columns['ip'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['port'] = array('type' => 'int');
        $this->_columns['project_id'] = array('type' => 'int', 'default' => 0);
        $this->_columns['commit'] = array('type' => 'char', 'size' => 32, 'default' => '');
        // status: 0-unused,
        //         1-webprocessing, 2-cronprocessing
        //         10-webnode, 11-cronnode
        $this->_columns['status'] = array('type' => 'tinyint', 'note' => array(
            0 => 'unused',
            1 => 'WebNode processing',
            2 => 'CronNode processing',
            10 => 'WebNode',
            11 => 'CronNode',
            100 => 'Stop',
            101 => 'Over',
        ));
        $this->_columns['created_at'] = array('type' => 'int');
        $this->_columns['start_at'] = array('type' => 'int', 'default' => 0);
        $this->_columns['access_at'] = array('type' => 'int', 'default' => 0);

        $this->_relations['project'] = array('rel' => 'has_one', 'type' => 'Project', 'foreign_key' => 'project_id');

        $this->addIndex('projectid_status_commit', array(
            'project_id',
            'status',
            'commit',
        ));
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
        if (false === $session) {
            throw new Exception('connect failed');
        }
        $ret = ssh2_auth_pubkey_file($session, 'root', WEB_PUBLIC_KEYFILE, WEB_KEYFILE);
        if (false === $session) {
            throw new Exception('ssh key is wrong');
        }
        $stream = ssh2_exec($session, "init $port");
        stream_set_blocking($stream, true);
        $ret = stream_get_contents($stream);
        if (!$ret = json_decode($ret)) {
            throw new Exception('result is not json');
        }
        if ($ret->error) {
            throw new Exception('init failed, message: ' . $ret->message);
        }
        WebNode::insert(array(
            'ip' => ip2long($ip),
            'port' => $port + 20000,
            'status' => WebNode::STATUS_UNUSED,
        )); 
    }

    /**
     * updateNodeInfo 把所有的 WebNode 檢查一次，把 cache 的時間和 counter 更新到 db，清除異常的 node 等
     *
     * @return void
     */
    public static function updateNodeInfo()
    {
        foreach (WebNode::search(1) as $node) {
            // 更新 access_at
            $node->update(array('access_at' => $node->getAccessAt()));

            // 放出 commit 版本不正確的 commit
            if ($project = $node->project) {
                if ($node->status == WebNode::STATUS_WEBNODE and $project->commit != $node->commit) {
                    // TODO: log it
                    $node->markAsUnused();
                }
            }

            // 如果是 cronnode or webnode 卻沒有任何 process 就 end
            if (in_array($node->status, array(WebNode::STATUS_CRONNODE, WebNode::STATUS_WEBNODE))) {
                $processes = $node->getNodeProcesses();
                if (0 == count($processes)) {
                    // TODO: log it
                    $node->markAsUnused();
                }
            }

            // 如果 processing node 太久也要踢掉
            if (in_array($node->status, array(WebNode::STATUS_CRONPROCESSING, WebNode::STATUS_WEBPROCESSING)) and (time() - $node->start_at) > 300) {
                $processes = $node->getNodeProcesses();
                if (0 == count($processes)) {
                    // TODO: log it
                    $node->markAsUnused();
                } else {
                    // TODO: log it
                }
            }
        }
    }
}
