<?php

class PgsqldbController extends Pix_Controller
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
        list(, /*pgsqldb*/, /*detail*/, $id) = explode('/', $this->getURI());
        if (!$addon = Addon_PgSQLDB::find(intval($id))) {
            return $this->alert('Addon not found', '/');
        }

        if (!$addon->isMember($this->user) and !$this->user->isAdmin()) {
            return $this->alert('Project not found', '/');
        }

        $this->view->addon_pgsqldb = $addon;
    }

    public function addprojectAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: log it
            return $this->alert('error', '/');
        }

        list(, /*pgsqldb*/, /*addproject*/, $id) = explode('/', $this->getURI());
        if (!$addon = Addon_PgSQLDB::find(intval($id))) {
            return $this->alert('Addon not found', '/');
        }

        if (!$addon->isAdmin($this->user)) {
            return $this->alert('Addon not found', '/');
        }

        if (!$project = Project::find_by_name(strval($_POST['project']))) {
            return $this->alert('Project not found', '/pgsqldb/detail/' . $addon->id);
        }
        $addon->addProject($project, $_POST['readonly'] ? 1 : 0);

        return $this->redirect('/pgsqldb/detail/' . $addon->id);
    }

    public function editnoteAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: log it
            return $this->alert('error', '/');
        }

        list(, /*pgsqldb*/, /*editnote*/, $id, $key) = explode('/', $this->getURI());
        if (!$addon = Addon_pgSQLDB::find(intval($id))) {
            return $this->alert('Addon not found', '/');
        }

        if (!$addon->isAdmin($this->user)) {
            return $this->alert('Addon not found', '/');
        }

        $addon->setEAV('note', strval($_POST['note']));
        return $this->redirect('/pgsqldb/detail/' . $addon->id);
    }
}
