<?php

/**
 * Pix_HttpResponse 一些 PHP 對 HTTP 作事的動作，為了方便加上 hook 追蹤用的 function
 * 
 * @copyright 2003-2010 PIXNET
 * @author Shang-Rung Wang <srwang@pixnet.tw> 
 */
class Pix_HttpResponse
{
    static protected function runHook($action, $args)
    {
	if (function_exists('PixHttpResponseHook_' . $action)) {
	    $hook = 'PixHttpResponseHook_' . $action;
	    call_user_func_array($hook, $args);
	}
    }

    static public function redirect($url, $code = 302)
    {
	header("Location: $url", true, $code);
	self::runHook('redirect', func_get_args());
    }

    static public function setcookie($name, $value, $expire = 0, $path = '/', $domain = null)
    {
	if (is_null($domain)) {
	    $domain = $_SERVER['HTTP_HOST'];
	}
	setcookie($name, $value, $expire, $path, $domain);
	self::runHook('setcookie', func_get_args());
    }
}
