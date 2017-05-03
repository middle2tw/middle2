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

    public function signupAction()
    {
        if (!getenv('SIGNUP_ENABLE')) {
            return $this->alert("Signup is not allowed.", '/');
        }
    }

    public function sendsignupAction()
    {
        if (!getenv('SIGNUP_ENABLE')) {
            return $this->alert("Signup is not allowed.", '/');
        }
        $email = $_POST['email'];

        try {
            SignupConfirm::sendSignupConfirm($email);
        } catch (Exception $e) {
            return $this->alert($e->getMessage(), '/index/signup');
        }

        return $this->alert("已寄出認證信，請至您的信箱點擊認證連結", "/");
    }

    public function signupconfirmAction()
    {
        if (!getenv('SIGNUP_ENABLE')) {
            return $this->alert("Signup is not allowed.", '/');
        }

        $mail = $_GET['mail'];
        $expired_at = $_GET['expired_at'];
        $sig = $_GET['sig'];

        if ($expired_at < time()) {
            return $this->alert('signup is expired', '/index/signup');
        }

        if (!$sc = SignupConfirm::search(array('email' => $mail))->order('created_at DESC')->first()) {
            return $this->alert('signup is not found', '/index/signup');
        }

        if (crc32($mail . $expired_at . $sc->code) != $sig) {
            return $this->alert('signup is not valid', '/index/signup');
        }

        $this->view->mail = $mail;
        $this->view->expired_at = $expired_at;
        $this->view->sig = $sig;
    }

    public function signupfinalAction()
    {
        if (!getenv('SIGNUP_ENABLE')) {
            return $this->alert("Signup is not allowed.", '/');
        }

        $mail = $_GET['mail'];
        $expired_at = $_GET['expired_at'];
        $sig = $_GET['sig'];

        if ($expired_at < time()) {
            return $this->alert('signup is expired', '/index/signup');
        }

        if (!$sc = SignupConfirm::search(array('email' => $mail))->order('created_at DESC')->first()) {
            return $this->alert('signup is not found', '/index/signup');
        }

        if (crc32($mail . $expired_at . $sc->code) != $sig) {
            return $this->alert('signup is not valid', '/index/signup');
        }

        $url = '/index/signupconfirm?mail=' . urlencode($mail) . '&expired_at=' . intval($expired_at) . '&sig=' . intval($sig);

        if (strlen($_POST['password']) < 4) {
            return $this->alert('your password is too short', $url);
        }
        if ($_POST['password'] != $_POST['repassword']) {
            return $this->alert('password is not the same', $url);
        }


        $u = User::insert(array(
            'name' => $mail,
            'status' => 1,
        ));
        $u->setPassword($_POST['password']);

        Logger::logOne(array('category' => "user-signup", 'message' => json_encode(array(
            'mail' => $mail,
            'message' => $_POST['message'],
            'time' => date('c'),
            'ip' => $_SERVER['REMOTE_ADDR'],
        ))));

        return $this->alert('註冊成功，請至首頁登入', '/');
    }
}
