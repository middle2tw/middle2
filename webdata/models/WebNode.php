<?php

class WebNodeRow extends Pix_Table_Row
{
    /**
     * markAsUnused 將這個 node 標為需要 reset, 不能再做任何事
     * 
     * @access public
     * @return void
     */
    public function markAsUnused()
    {
        Logger::logOne(array('category' => "app-{$this->project->name}-node", 'message' => json_encode(array(
            'time' => microtime(true),
            'ip' => $this->ip,
            'port' => $this->port,
            'commit' => $this->commit,
            'spent' => (time() - $this->start_at),
            'type' => WebNode::getNodeTypeByStatus($this->status),
            'status' => 'over',
        ))));

        $this->update(array(
            'project_id' => 0,
            'commit' => '',
            'status' => WebNode::STATUS_OVER,
        ));
    }

    public function getStatusWord()
    {
        $node_status = WebNode::getTable()->_columns['status']['note'];
        $word = isset($node_status[$this->status]) ? $node_status[$this->status] : 'Unknown';
        if ($this->status == WebNode::STATUS_CRONNODE) {
            $word .= ':' . $this->getEAV('job');
        }
        return $word;
    }

    public function getServiceProject()
    {
        return Addon_Memcached::search(array('host' => long2ip($this->ip), 'port' => $this->port))->first()->project;
    }

    /**
     * markAsWait 將這個 node 標為 waiting, 之後同 repository 還可以用
     * 
     * @access public
     * @return void
     */
    public function markAsWait()
    {
        Logger::logOne(array('category' => "app-{$this->project->name}-node", 'message' => json_encode(array(
            'time' => microtime(true),
            'ip' => $this->ip,
            'port' => $this->port,
            'commit' => $this->commit,
            'type' => WebNode::getNodeTypeByStatus($this->status),
            'spent' => (time() - $this->start_at),
            'status' => 'wait',
        ))));

        $this->update(array(
            'status' => WebNode::STATUS_WAIT,
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

    public function resetNode()
    {
        $session = ssh2_connect(long2ip($this->ip), 22);
        if (false === $session) {
            throw new Exception('ssh connect failed');
        }
        $ret = ssh2_auth_pubkey_file($session, 'root', WEB_PUBLIC_KEYFILE, WEB_KEYFILE);
        if (false === $ret) {
            throw new Exception('key failed');
        }
        $stream = ssh2_exec($session, "shutdown " . ($this->port - 20000));
        stream_set_blocking($stream, true);
        $ret = stream_get_contents($stream);
        if (!$ret = json_decode($ret)) {
           //throw new Exception('wrong json');
        }
        if ($ret->error) {
            //throw new Exception('json error');
        }

        $stream = ssh2_exec($session, "init " . ($this->port - 20000));
        stream_set_blocking($stream, true);
        $ret = stream_get_contents($stream);
        if (!$ret = json_decode($ret)) {
            throw new Exception('wrong json');
        }
        if ($ret->error) {
            throw new Exception('json error');
        }
        $this->update(array(
            'status' => WebNode::STATUS_UNUSED,
        ));

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

    public function updateAccessAt()
    {
        $c = new Pix_Cache;
        $cache_key = "WebNode:access_at:{$this->ip}:{$this->port}";
        $c->set($cache_key, time());
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

    public function runJob($command, $options = array())
    {
        $this->setEAV('job', $command);

        $session = ssh2_connect(long2ip($this->ip), 22);
        if (false === $session) {
            throw new Exception('connect failed');
        }
        $ret = ssh2_auth_pubkey_file($session, 'root', WEB_PUBLIC_KEYFILE, WEB_KEYFILE);
        if (false === $ret) {
            throw new Exception('ssh key is wrong');
        }

        Logger::logOne(array('category' => "app-{$this->project->name}-node", 'message' => json_encode(array(
            'time' => microtime(true),
            'ip' => $this->ip,
            'port' => $this->port,
            'commit' => $this->commit,
            'type' => 'cron',
            'status' => 'start',
            'command' => $command,
        ))));

        $node_id = $this->port - 20000;
        if ($options['term']) {
            $stream = ssh2_exec($session, "run {$this->project->name} {$node_id} " . urlencode($command), $options['term'], array(), $options['width'], $options['height']);
        } else {
            $stream = ssh2_exec($session, "run {$this->project->name} {$node_id} " . urlencode($command));
        }
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
    const STATUS_WAIT = 102; // 這個 node 還保有完整的某個 repository 環境，還可以繼續使用
    const STATUS_SERVICE = 103; // 被 service 拿去用了，這些是不會死的

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
            102 => 'Wait',
            103 => 'Service',
        ));
        $this->_columns['created_at'] = array('type' => 'int');
        $this->_columns['start_at'] = array('type' => 'int', 'default' => 0);
        $this->_columns['access_at'] = array('type' => 'int', 'default' => 0);

        $this->_relations['project'] = array('rel' => 'has_one', 'type' => 'Project', 'foreign_key' => 'project_id');
        $this->_relations['eavs'] = array('rel' => 'has_many', 'type' => 'WebNodeEAV', 'foreign_key' => array('ip', 'port'));

        $this->addRowHelper('Pix_Table_Helper_EAV', array('getEAV', 'setEAV'));

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
                if (in_array($node->status, array(WebNode::STATUS_WEBNODE, WebNode::STATUS_WAIT)) and $project->commit != $node->commit) {
                    $node->markAsUnused();
                }
            }

            // 如果是 cronnode or webnode 卻沒有任何 process 就 end
            if (in_array($node->status, array(WebNode::STATUS_CRONNODE, WebNode::STATUS_WEBNODE))) {
                $processes = $node->getNodeProcesses();
                if (0 == count($processes)) {
                    trigger_error("{$node->ip}:{$node->port} had no alive process, release it", E_USER_WARNING);
                    $node->markAsUnused();
                }
            }

            // WebNode 超過一小時沒人看就 end
            if (in_array($node->status, array(WebNode::STATUS_WEBNODE)) and (time() - $node->getAccessAt()) > 3600) {
                if ($project = $node->project and $project->getEAV('always-alive')) {
                } else {
                    $node->markAsUnused();
                }
            }

            // 如果 processing node 太久也要踢掉
            if (in_array($node->status, array(WebNode::STATUS_CRONPROCESSING, WebNode::STATUS_WEBPROCESSING)) and (time() - $node->getAccessAt()) > 600 and (time() - $node->start_at) > 600) {
                $processes = $node->getNodeProcesses();
                if (0 == count($processes)) {
                    trigger_error("{$node->ip}:{$node->port}(status=processing) had no alive process, release it", E_USER_WARNING);
                    $node->markAsUnused();
                } else {
                    trigger_error("{$node->ip}:{$node->port}(status=processing) is processing too long, start_at: " . date('c', $node->start_at), E_USER_WARNING);
                }
            }

            // Wait node 保留兩小時
            if (in_array($node->status, array(WebNode::STATUS_WAIT)) and (time() - $node->getAccessAt()) > 7200) {
                $node->markAsUnused();
            }

            // 如果是 over 要放出來
            if (in_array($node->status, array(WebNode::STATUS_OVER))) {
                $node->resetNode();
            }
        }
    }

    public static function getNodeTypeByStatus($status)
    {
        $type_map = array(
            WebNode::STATUS_WEBNODE => 'web',
            WebNode::STATUS_CRONNODE => 'cron',
        );
        return array_key_exists($status, $type_map) ? $type_map[$status] : ("other-{$status}");
    }
}
