<?php

class Addon_ElasticRow extends Pix_Table_Row
{
    public function saveProjectVariable($key = 'SEARCH_URL')
    {
        try {
            $this->project->variables->insert(array(
                'key' => $key,
                'value' => "Addon_Elastic:{$this->id}:SearchURL",
                'is_magic_value' => 1,
            ));
        } catch (Pix_Table_DuplicateException $e) {
            $this->project->variables->search(array(
                'key' => $key,
            ))->update(array(
                'value' => "Addon_MySQLDB:{$this->id}:SearchURL",
                'is_magic_value' => 1,
            ));
        }
    }

    public function getSearchURL()
    {
        return 'https://elastic.middle2.com/' . $this->index . ':' . $this->secret;
    }
}

class Addon_Elastic extends Pix_Table
{
    public function init()
    {
        $this->_name = 'addon_elastic';
        $this->_rowClass = 'Addon_ElasticRow';

        $this->_primary = array('id');

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true);
        $this->_columns['project_id'] = array('type' => 'int');
        $this->_columns['host'] = array('type' => 'varchar', 'size' => 255);
        $this->_columns['index'] = array('type' => 'varchar', 'size' => 32);
        $this->_columns['secret'] = array('type' => 'int');

        $this->_relations['project'] = array('rel' => 'has_one', 'type' => 'Project', 'foreign_key' => 'project_id');

        $this->addIndex('project_id', array('project_id'));
    }

    public static function addDB($project, $key = 'SEARCH_URL')
    {
        if ($addon = self::search(array('project_id' => $project->id))->first()) {
            $addon->saveProjectVariable($key);
            return;
        }

        $ips = Hisoku::getIPsByGroup('search');
        $host = array_pop($ips);
        $index = $project->name;
        $secret = crc32(uniqid());

        $addon = self::insert(array(
            'project_id' => $project->id,
            'host' => $host,
            'index' => $index,
            'secret' => $secret,
        ));
        $addon->saveProjectVariable($key);
    }
}
