<?php

class MysqldbController extends Pix_Controller
{
    public function init()
    {
        if (!$this->user = Hisoku::getLoginUser()) {
            return $this->rediect('/');
        }
        $this->view->user = $this->user;
    }

    public function detailAction()
    {
        list(, /*mysqldb*/, /*detail*/, $id) = explode('/', $this->getURI());
        if (!$addon = Addon_MySQLDB::find(intval($id))) {
            return $this->alert('Addon not found', '/');
        }

        if (!$addon->isMember($this->user) and !$this->user->isAdmin()) {
            return $this->alert('Project not found', '/');
        }

        $this->view->addon_mysqldb = $addon;
    }

    public function addprojectAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: log it
            return $this->alert('error', '/');
        }

        list(, /*mysqldb*/, /*addproject*/, $id) = explode('/', $this->getURI());
        if (!$addon = Addon_MySQLDB::find(intval($id))) {
            return $this->alert('Addon not found', '/');
        }

        if (!$addon->isAdmin($this->user)) {
            return $this->alert('Addon not found', '/');
        }

        if (!$project = Project::find_by_name(strval($_POST['project']))) {
            return $this->alert('Project not found', '/mysqldb/detail/' . $addon->id);
        }
        $addon->addProject($project, $_POST['readonly'] ? 1 : 0);

        return $this->redirect('/mysqldb/detail/' . $addon->id);
    }

    public function editnoteAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: log it
            return $this->alert('error', '/');
        }

        list(, /*mysqldb*/, /*editnote*/, $id, $key) = explode('/', $this->getURI());
        if (!$addon = Addon_MySQLDB::find(intval($id))) {
            return $this->alert('Addon not found', '/');
        }

        if (!$addon->isAdmin($this->user)) {
            return $this->alert('Addon not found', '/');
        }

        $addon->setEAV('note', strval($_POST['note']));
        return $this->redirect('/mysqldb/detail/' . $addon->id);
    }
}
