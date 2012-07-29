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
}
