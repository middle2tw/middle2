<?php

class CronJob extends Pix_Table
{
    public function init()
    {
        $this->_name = 'cron_job';

        $this->_primary = 'id';

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true);
        $this->_columns['project_id'] = array('type' => 'int');
        // 1 - 10minutes, 2 - hourly, 3 - daily
        $this->_columns['period'] = array('type' => 'tinyint');
        $this->_columns['start_at'] = array('type' => 'int');
        $this->_columns['last_run_at'] = array('type' => 'int');
        $this->_columns['job'] = array('type' => 'text');

        $this->addIndex('project', array('project_id'));
        $this->addIndex('period_lastrunat', array('period', 'last_run_at'));
    }
}
