<?php

class AdminController extends Pix_Controller
{
    public function init()
    {
        // TODO: admin 應該要再登入一次，最好還意加上 2-step
        if (!$this->user = Hisoku::getLoginUser()) {
            return $this->rediect('/');
        }
        if (!$this->user->isAdmin()) {
            return $this->rediect('/');
        }
        $this->view->user = $this->user;
    }

    public function indexAction()
    {
    }
}
