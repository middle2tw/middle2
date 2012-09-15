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

        // from http://myregexp.com/examples.html
        if (!preg_match('#^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$#', $_POST['domain'])) {
            // TODO: 404
            return $this->redirect('/');
        }

        $project->custom_domains->insert(array(
            'domain' => strval($_POST['domain']),
        ));

        return $this->redirect('/project/detail/' . $project->name);
    }
}
