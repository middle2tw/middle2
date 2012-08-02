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
        if (!$u = User::find_by_name(strval($_POST['user'])) or !$u->verifyPassword($_POST['password'])) {
            // TODO: alert
            return $this->redirect('/');
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
