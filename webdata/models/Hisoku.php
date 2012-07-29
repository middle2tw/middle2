<?php

class Hisoku
{
    public static function getLoginUser()
    {
        if ($u = User::find(intval(Pix_Session::get('user')))) {
            return $u;
        }
        return false;
    }
}
