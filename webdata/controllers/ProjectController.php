<?php

class ProjectController extends Pix_Controller
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
        list(, /*project*/, /*detail*/, $name) = explode('/', $this->getURI());

        if (!$project = Project::find_by_name($name)) {
            // TODO: 404
            return $this->redirect('/');
        }

        if (!count($project->members->search(array('user_id' => $this->user->id)))) {
            // TODO 404
            return $this->redirect('/');
        }

        $this->view->project = $project;

    }

    public function deletedomainAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: error
            return $this->redirect('/');
        }

        list(, /*project*/, /*adddomain*/, $name) = explode('/', $this->getURI());
        if (!$project = Project::find_by_name($name)) {
            // TODO: 404
            return $this->redirect('/');
        }

        if (!$domain = $project->custom_domains->search(array('domain' => $_GET['domain']))->first()) {
            // TODO: 404
            return $this->redirect('/');
        }

        $domain->delete();
        return $this->redirect('/project/detail/' . $project->name);
    }

    public function adddomainAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: error
            return $this->redirect('/');
        }

        list(, /*project*/, /*adddomain*/, $name) = explode('/', $this->getURI());
        if (!$project = Project::find_by_name($name)) {
            // TODO: 404
            return $this->redirect('/');
        }

        // from http://regexlib.com/REDetails.aspx?regexp_id=306
        if (!preg_match('#^(([\w][\w\-\.]*)\.)?([\w][\w\-]+)(\.([\w][\w\.]*))?$#', $_POST['domain'])) {
            // TODO: 404
            return $this->redirect('/');
        }

        $project->custom_domains->insert(array(
            'domain' => strval($_POST['domain']),
        ));

        return $this->redirect('/project/detail/' . $project->name);
    }

    public function deletevariableAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: error
            return $this->redirect('/');
        }

        list(, /*project*/, /*addvariable*/, $name, $key) = explode('/', $this->getURI());
        if (!$project = Project::find_by_name($name)) {
            // TODO: 404
            return $this->redirect('/');
        }

        if (!$variable = $project->variables->search(array('key' => $key))->first()) {
            // TODO: 404
            return $this->redirect('/');
        }

        $variable->delete();
        return $this->redirect('/project/detail/' . $project->name);
    }

    public function addvariableAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: error
            return $this->redirect('/');
        }

        list(, /*project*/, /*addvariable*/, $name) = explode('/', $this->getURI());
        if (!$project = Project::find_by_name($name)) {
            // TODO: 404
            return $this->redirect('/');
        }

        // TODO: check valid key & value
        $project->variables->insert(array(
            'key' => strval($_POST['key']),
            'value' => strval($_POST['value']),
        ));

        return $this->redirect('/project/detail/' . $project->name);
    }

    public function editnoteAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: error
            return $this->redirect('/');
        }

        list(, /*project*/, /*editnote*/, $name, $key) = explode('/', $this->getURI());
        if (!$project = Project::find_by_name($name)) {
            // TODO: 404
            return $this->redirect('/');
        }

        $project->setEAV('note', strval($_POST['note']));
        return $this->redirect('/project/detail/' . $project->name);
    }

    public function editvariableAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: error
            return $this->redirect('/');
        }

        list(, /*project*/, /*editvariable*/, $name, $key) = explode('/', $this->getURI());
        if (!$project = Project::find_by_name($name)) {
            // TODO: 404
            return $this->redirect('/');
        }

        if (!$variable = $project->variables->search(array('key' => $key))->first()) {
            // TODO: 404
            return $this->redirect('/');
        }

        $variable->update(array(
            'value' => strval($_POST['value']),
        ));
        return $this->redirect('/project/detail/' . $project->name);
    }
}
