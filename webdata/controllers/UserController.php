<?php

class UserController extends Pix_Controller
{
    public function init()
    {
        if (!$this->user = Hisoku::getLoginUser()) {
            return $this->rediect('/');
        }
        $this->view->user = $this->user;

        if (getenv('TRY_MODE')) {
            $this->try_mode = getenv('TRY_MODE');
            $this->view->try_mode = $this->try_mode;
            $this->project_limit = 3;
            $this->view->project_limit = $this->project_limit;
        }
    }

    public function indexAction()
    {
        $this->view->project_count = Project::search(array('created_by' => $this->user->id))->count();
    }

    public function deletekeyAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: error
            return $this->redirect('/');
        }

        list(, /*user*/, /*deletekey*/, $id) = explode('/', $this->getURI());
        if (!$userkey = $this->user->keys->search(array('id' => $id))->first()) {
            // TODO: error
            return $this->redirect('/');
        }

        $userkey->delete();
        return $this->redirect('/');
    }

    public function addkeyAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: error
            return $this->redirect('/');
        }

        try {
            $this->user->addKey($_POST['key']);
        } catch (InvalidException $e) {
            // TODO: error
        } catch (Pix_Table_DuplicateException $e) {
            // TODO: error
        }
        return $this->redirect('/');
    }

    public function addprojectAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: error
            return $this->redirect('/');
        }

        if ($this->try_mode) {
            $project_count = Project::search(array('created_by' => $this->user->id))->count();
            if (intval($project_count) >= $this->project_limit) {
                return $this->alert('目前為試用模式，project 數量上限為 ' . $this->project_limit, '/');
            }
        }

        try {
            $project = $this->user->addProject();
        } catch (InvalidException $e) {
            // TODO: error
            return $this->redirect('/');
        } catch (Pix_Table_DuplicateException $e) {
            // TODO: error
            return $this->redirect('/');
        }

        $project->setEAV('note', strval($_POST['name']));

        return $this->redirect('/');
    }

    public function changepasswordAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            return $this->alert('Error', '/');
        }

        if (!$this->user->verifyPassword($_POST['oldpassword'])) {
            Logger::log(array(array('category' => 'login', 'message' => time() . " change-password-wrong-password {$_SERVER['REMOTE_ADDR']} user=" . urlencode($this->user->name) . ",agent=" . urlencode($_SERVER['HTTP_USER_AGENT']))));
            return $this->alert('Wrong password', '/');
        }

        if ($_POST['newpassword'] != $_POST['newpassword2']) {
            return $this->alert('Password mismatch', '/');
        }

        if (strlen($_POST['newpassword']) < 4) {
            return $this->alert('Password is too short', '/');
        }

        $this->user->setPassword($_POST['newpassword']);
        Logger::log(array(array('category' => 'login', 'message' => time() . " change-password {$_SERVER['REMOTE_ADDR']} user=" . urlencode($this->user->name) . ",agent=" . urlencode($_SERVER['HTTP_USER_AGENT']))));

        return $this->alert('success!', '/');

    }

    public function nodbAction()
    {
        return $this->alert('沒有任何 database 可用', '/');
    }
}
