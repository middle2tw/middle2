<?php

class ProjectVariableRow extends Pix_Table_Row
{
    protected function _releaseNodes()
    {
        foreach ($this->project->webnodes as $webnode) {
            $webnode->update(array(
                'project_id' => 0,
                'commit' => '',
                'status' => WebNode::STATUS_OVER,
            ));
        }
    }

    public function postSave()
    {
        $this->_releaseNodes();
    }

    public function postDelete()
    {
        $this->_releaseNodes();
    }
}

class ProjectVariable extends Pix_Table
{
    public function init()
    {
        $this->_name = 'project_variable';
        $this->_primary = array('project_id', 'key');
        $this->_rowClass = 'ProjectVariableRow';

        $this->_columns['project_id'] = array('type' => 'int');
        $this->_columns['key'] = array('type' => 'varchar', 'size' => 32);
        $this->_columns['value'] = array('type' => 'text');

        $this->_relations['project'] = array('rel' => 'has_one', 'type' => 'Project', 'foreign_key' => 'project_id');
    }
}
