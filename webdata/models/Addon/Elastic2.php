<?php

class Addon_Elastic2Row extends Pix_Table_Row
{
    public function removeElastic()
    {
        Elastic::dropUser($this->user);
    }

    public function updateElastic()
    {
        Elastic::login($this->getURL(), getenv('ELASTIC_ADMIN_USER'), getenv('ELASTIC_ADMIN_PASSWORD'));
        Elastic::createUser($this->user, $this->password, $this->prefix);
    }

    public function saveProjectVariable()
    {
        foreach (['ELASTIC_URL', 'ELASTIC_USER', 'ELASTIC_PASSWORD', 'ELASTIC_PREFIX'] as $k) {
            try {
                $this->project->variables->insert(array(
                    'key' => $k,
                    'value' => "Addon_Elastic2:{$this->id}:{$k}",
                    'is_magic_value' => 1,
                ));
            } catch (Pix_Table_DuplicateException $e) {
                $this->project->variables->search(array(
                    'key' => $k,
                ))->update(array(
                    'value' => "Addon_Elastic2:{$this->id}:{$k}",
                    'is_magic_value' => 1,
                ));
            }
        }
    }

    public function getURL()
    {
        return sprintf("https://%s:9200", $this->host);
    }

}

class Addon_Elastic2 extends Pix_Table
{
    public function init()
    {
        $this->_name = 'addon_elastic2';
        $this->_rowClass = 'Addon_Elastic2Row';

        $this->_primary = array('id');

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true);
        $this->_columns['project_id'] = array('type' => 'int');
        $this->_columns['host'] = array('type' => 'varchar', 'size' => 255);
        $this->_columns['prefix'] = array('type' => 'varchar', 'size' => 16);
        $this->_columns['user'] = array('type' => 'varchar', 'size' => 32);
        $this->_columns['password'] = array('type' => 'varchar', 'size' => 32);

        $this->_relations['project'] = array('rel' => 'has_one', 'type' => 'Project', 'foreign_key' => 'project_id');

        $this->addIndex('project_id', array('project_id'));
    }

    public static function addDB($project)
    {
        if ($addon = self::search(array('project_id' => $project->id))->first()) {
            $addon->saveProjectVariable();
            return;
        }

        // TODO: from config
        $host = 'elastic-2.middle2.com';
        $prefix = strtolower(Hisoku::uniqid(10));
        $password = Hisoku::uniqid(20);

        $addon = self::insert(array(
            'project_id' => $project->id,
            'host' => $host,
            'prefix' => $prefix,
            'user' => $project->name,
            'password' => $password,
        ));
        $addon->saveProjectVariable();
        $addon->updateElastic();
    }
}
