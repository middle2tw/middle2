<?php

class Hisoku
{
    public function getLoginUser()
    {
        if ($u = User::find(Pix_Session::get('user'))) {
            return $u;
        }
        return false;
    }
}
