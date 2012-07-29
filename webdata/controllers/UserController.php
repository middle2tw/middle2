<?php

class UserController extends Pix_Controller
{
    public function init()
    {
        if (!$this->guest = Hisoku::getLoginUser()) {
            return $this->rediect('/');
        }
    }

    public function indexAction()
    {
    }
}
