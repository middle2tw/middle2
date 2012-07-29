<?php

/**
 * Pix_Session_Core_Default 
 * 
 * @uses Pix
 * @uses _Session_Core
 * @package Pix_Session
 * @version $id$
 * @copyright 2003-2010 PIXNET
 * @author Shang-Rung Wang <srwang@pixnet.tw> 
 * @license PHP Version 3.0 {@link http://www.php.net/license/3_0.txt}
 */
class Pix_Session_Core_Default extends Pix_Session_Core
{
    public function __construct($config = array())
    {
	if (isset($config['save_path'])) {
	    ini_set('session.save_path', $config['save_path']);
	}

	if (isset($config['save_handler'])) {
	    ini_set('session.save_handler', $config['save_handler']);
	}

	if (session_id()){
	    throw new Exception('You shouldn\'t use session_start() before!');
	}
	session_start();
    }

    public function set($key, $value)
    {
	$_SESSION[$key] = $value;
    }

    public function get($key)
    {
	return $_SESSION[$key];
    }

    public function delete($key)
    {
	unset($_SESSION[$key]);
    }

    public function clear()
    {
        session_destroy();
    }
}
