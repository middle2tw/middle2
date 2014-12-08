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
            'start_at' => time(),
        ));
        $node->updateAccessAt();
        $node->update(array('cron_id' => $this->id));
        $ret = $node->runJob($this->job);

        stream_set_blocking($ret->stdout, true);
        stream_set_blocking($ret->stderr, true);
        stream_set_blocking($ret->stdio, true);

        $output = (stream_get_contents($ret->stdout));
        $output = (stream_get_contents($ret->stderr));
        $output = (stream_get_contents($ret->stdio));
        $node->markAsWait();
        $node->update(array('cron_id' => 0));
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
    );

    public function init()
    {
        $this->_name = 'cron_job';
        $this->_rowClass = 'CronJobRow';

        $this->_primary = 'id';

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true);
        $this->_columns['project_id'] = array('type' => 'int');
        // 0 - disable, 1 - 10minutes, 2 - hourly, 3 - daily, 4 - 1munute
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
                $pid = pcntl_fork();

                if ($pid) {
                    Pix_Table_Db_Adapter_MysqlConf::resetConnect();
                    continue;
                }

                if (function_exists('setproctitle')) {
                    setproctitle("php-fpm: Cron {$cronjob->project->name}: {$cronjob->job}");
                }
                $cronjob->runJob();
                exit;
            }
        }
        $status = 0;
        pcntl_wait($status);
    }
}
