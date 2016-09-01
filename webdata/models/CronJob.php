<?php

class CronJobRow extends Pix_Table_Row
{
    public function getEAVs()
    {
        return EAV::search(array('table' => 'CronJob', 'id' => $this->id));
    }

    public function runJob()
    {
        $this->update(array('last_run_at' => time()));
        $node = $this->project->getCronNode();

        $node->update(array(
            'status' => WebNode::STATUS_CRONNODE,
        ));
        $node->updateAccessAt();
        $node->update(array('cron_id' => $this->id));
        $ret = $node->runJob($this->job);

        stream_set_blocking($ret->stderr, true);
        stream_set_blocking($ret->stdio, true);

        $output = new StdClass;
        $output->stderr = (stream_get_contents($ret->stderr));
        $lines = explode("\n", trim(stream_get_contents($ret->stdio)));
        $return_code = array_pop($lines);
        $output->stdout  = implode("\n", $lines);
        $output->status = json_decode($return_code);

        $recent_logs = json_decode($this->getEAV('recent_logs')) ?: array();
        array_push($recent_logs, $output);
        $recent_logs = array_slice($recent_logs, 0, 10);
        $this->setEAV('recent_logs', json_encode($recent_logs));

        if ($output->status->code != 0) {
            $recent_logs = json_decode($this->getEAV('recent_error_logs')) ?: array();
            array_push($recent_logs, $output);
            $recent_logs = array_slice($recent_logs, 0, 10);
            $this->setEAV('recent_error_logs', json_encode($recent_logs));
        }

        $node->markAsWait();
        $node->update(array('cron_id' => 0));
        return $output;
    }

    public function getNextRunAt()
    {
        if ($this->start_at > time()) {
            return $this->start_at;
        }

        if (!$this->last_run_at) {
            return time();
        }

        return $this->last_run_at + CronJob::getPeriodTime($this->period);
    }
}

class CronJob extends Pix_Table
{
    protected static $_period_map = array(
        0 => 0,
        1 => 600,
        2 => 3600,
        3 => 86400,
        4 => 60,
        99 => 0,
    );

    public function init()
    {
        $this->_name = 'cron_job';
        $this->_rowClass = 'CronJobRow';

        $this->_primary = 'id';

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true);
        $this->_columns['project_id'] = array('type' => 'int');
        // 0 - disable, 1 - 10minutes, 2 - hourly, 3 - daily, 4 - 1munute, 99 - worker
        $this->_columns['period'] = array('type' => 'tinyint');
        $this->_columns['start_at'] = array('type' => 'int', 'default' => 0);
        $this->_columns['last_run_at'] = array('type' => 'int');
        $this->_columns['job'] = array('type' => 'text');

        $this->_relations['project'] = array('rel' => 'has_one', 'type' => 'Project', 'foreign_key' => 'project_id');

        $this->addIndex('project', array('project_id'));
        $this->addIndex('period_lastrunat', array('period', 'last_run_at'));

        $this->_hooks['eavs'] = array('get' => 'getEAVs');

        $this->addRowHelper('Pix_Table_Helper_EAV', array('getEAV', 'setEAV'));
    }

    public static function getPeriodTime($period_id)
    {
        return self::$_period_map[$period_id];
    }

    public static function runPendingJobs()
    {
        foreach (self::$_period_map as $period_id => $time) {
            if (!$time) {
                continue;
            }
            // 多給 5 秒的彈性..這樣才不會 10 分鐘 cron 跑到 11 分鐘
            foreach (self::search(array('period' => $period_id))->search("last_run_at < " . (5 + time() - $time)) as $cronjob) {
                if (WebNode::search(array('cron_id' => $cronjob))->count() >= 3) {
                    Hisoku::alert("Middle2 Cron Error", "Project {$cronjob->project->name} run cron {$cronjob->job} exceed 3 in a time");
                    continue;
                }
                $pid = pcntl_fork();

                if ($pid) {
                    Logger::logOne(array('category' => 'cron', 'message' => 'fork from PID=' . getmypid() . ', new PID=' . $pid . ", project={$cronjob->project->name} job={$cronjob->job}"));
                    Pix_Table_Db_Adapter_MysqlConf::resetConnect();
                    continue;
                }

                if (function_exists('setthreadtitle')) {
                    setthreadtitle("php-fpm: Cron {$cronjob->project->name}: {$cronjob->job}");
                }
                $cronjob->runJob();
                exit;
            }
        }
        $status = 0;
        pcntl_wait($status);
    }

    public function runWorker()
    {
        // 檢查所有 Worker 是否活著或是版本有更新
        foreach (self::search(array('period' => 99)) as $workerjob) {
            $nodes = WebNode::search(array('cron_id' => $workerjob->id));
            
            if (count($nodes) > 1) {
                // TODO: 跑了多隻 worker ，應該要砍掉一隻，未來再支援同時多隻 worker
            }

            $node = $nodes->first();

            if ($node) {
                // 線上跑的版本不相同，表示該砍掉重跑
                if ($node->commit != $workerjob->project->commit) {
                    // 砍掉重跑
                    $pid = pcntl_fork();

                    if ($pid) {
                        Pix_Table_Db_Adapter_MysqlConf::resetConnect();
                        continue;
                    }

                    if (function_exists('setthreadtitle')) {
                        setthreadtitle("php-fpm: Worker {$workerjob->project->name}: {$workerjob->job}");
                        error_log("php-fpm: Worker {$workerjob->project->name}: {$workerjob->job}");
                    }
                    Hisoku::alert("Middle2 Worker Notice", "Project {$workerjob->project->name} run worker {$workerjob->job}, with commit change");
                    $node->markAsUnused('commit change');
                    $node->resetNode();
                    $workerjob->runJob();
                    exit;
                }

                $processes = $node->getNodeProcesses();
                if (0 == count($processes)) {
                    // 沒有任何 process 了，應該要重跑 worker (不需要 resetNode ，因為在 WebNode 那邊會做)
                    $pid = pcntl_fork();

                    if ($pid) {
                        Pix_Table_Db_Adapter_MysqlConf::resetConnect();
                        continue;
                    }

                    if (function_exists('setthreadtitle')) {
                        setthreadtitle("php-fpm: Worker {$workerjob->project->name}: {$workerjob->job}");
                        error_log("php-fpm: Worker {$workerjob->project->name}: {$workerjob->job}");
                    }
                    Hisoku::alert("Middle2 Worker Notice", "Project {$workerjob->project->name} run worker {$workerjob->job} with no process found on " . long2ip($node->ip) . ":{$node->port}");
                    $workerjob->runJob();
                    exit;
                }
            } else {
                // 沒有任何 process 了，應該要重跑 worker (不需要 resetNode ，因為在 WebNode 那邊會做)
                $pid = pcntl_fork();

                if ($pid) {
                    Pix_Table_Db_Adapter_MysqlConf::resetConnect();
                    continue;
                }

                if (function_exists('setthreadtitle')) {
                    setthreadtitle("php-fpm: Worker {$workerjob->project->name}: {$workerjob->job}");
                    error_log("php-fpm: Worker {$workerjob->project->name}: {$workerjob->job}");
                }
                Hisoku::alert("Middle2 Worker Notice", "Project {$workerjob->project->name} run worker {$workerjob->job}");
                $workerjob->runJob();
                exit;
            }
        }
        $status = 0;

        foreach (WebNode::search("cron_id > 0") as $webnode) {
            // 如果找不到 job 了，或者是他已經變成 disable job 了，就殺了他
            if (!$job = CronJob::find($webnode->cron_id) or $job->period == 0) {
                $webnode->markAsUnused('job not found or is disabled');
            }
        }
    }
}
