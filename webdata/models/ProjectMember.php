<?php

class ProjectMember extends Pix_Table
{
    public function init()
    {
        $this->_name = 'project_member';
        $this->_primary = array('id');

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true);
        $this->_columns['project_id'] = array('type' => 'int');
        $this->_columns['user_id'] = array('type' => 'int');
        $this->_columns['is_admin'] = array('type' => 'int');

        $this->_indexes['project_user'] = array('type' => 'unique', 'columns' => array('project_id', 'user_id'));
        $this->_indexes['user_project'] = array('type' => 'unique', 'columns' => array('user_id', 'project_id'));

        $this->_relations['project'] = array('rel' => 'has_one', 'type' => 'Project', 'foreign_key' => 'project_id');
        $this->_relations['user'] = array('rel' => 'has_one', 'type' => 'User', 'foreign_key' => 'user_id');
    }
}
