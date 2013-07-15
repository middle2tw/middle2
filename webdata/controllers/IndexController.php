<?php

class IndexController extends Pix_Controller
{
    public function indexAction()
    {
        if (Hisoku::getLoginUser()) {
            return $this->redirect('/user');
        }
    }

    public function loginAction()
    {
        $alert_message = "Invalid user name or password";
        if (!$u = User::find_by_name(strval($_POST['user']))) {
            return $this->alert($alert_message, '/');
        }
       
        if (!$u->verifyPassword($_POST['password'])) {
            return $this->alert($alert_message, '/');
        }

        Pix_Session::set('user', $u->id);
        return $this->redirect('/');
    }

    public function logoutAction()
    {
        Pix_Session::delete('user');
        return $this->redirect('/');
    }
}
