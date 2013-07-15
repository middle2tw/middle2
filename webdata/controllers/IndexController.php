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
            Logger::log(array(array('category' => 'login', 'message' => time() . " user-not-found {$_SERVER['REMOTE_ADDR']} user=" . urlencode($_POST['user']) . ",agent=" . urlencode($_SERVER['HTTP_USER_AGENT']))));
            return $this->alert($alert_message, '/');
        }
       
        if (!$u->verifyPassword($_POST['password'])) {
            Logger::log(array(array('category' => 'login', 'message' => time() . " wrong-password {$_SERVER['REMOTE_ADDR']} user=" . urlencode($_POST['user']) . ",agent=" . urlencode($_SERVER['HTTP_USER_AGENT']))));
            return $this->alert($alert_message, '/');
        }

        Pix_Session::set('user', $u->id);
        Logger::log(array(array('category' => 'login', 'message' => time() . " ok {$_SERVER['REMOTE_ADDR']} user=" . urlencode($_POST['user']) . ",agent=" . urlencode($_SERVER['HTTP_USER_AGENT']))));
        return $this->redirect('/');
    }

    public function logoutAction()
    {
        $user = Hisoku::getLoginUser();
        Logger::log(array(array('category' => 'login', 'message' => time() . " logout {$_SERVER['REMOTE_ADDR']} user=" . urlencode($user->name) . ",agent=" . urlencode($_SERVER['HTTP_USER_AGENT']))));
        Pix_Session::delete('user');
        return $this->redirect('/');
    }
}
