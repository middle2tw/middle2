<?php

class Hisoku
{
    public function getView()
    {
        $view = new Pix_Partial(__DIR__ . '/../views');
        return $view;
    }

    public function getLoginUser()
    {
        if ($u = User::find(Pix_Session::get('user'))) {
            return $u;
        }
        return false;
    }
}
