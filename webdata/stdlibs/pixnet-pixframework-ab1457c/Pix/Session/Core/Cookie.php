<?php

/**
 * Pix_Session_Core_Cookie 
 * 
 * @uses Pix
 * @uses _Session_Core
 * @package Pix_Session
 * @version $id$
 * @copyright 2003-2010 PIXNET
 * @author Shang-Rung Wang <srwang@pixnet.tw> 
 * @license PHP Version 3.0 {@link http://www.php.net/license/3_0.txt}
 */
class Pix_Session_Core_Cookie extends Pix_Session_Core
{
    protected $_data = array();

    public function __construct($config = array())
    {
	Pix_Session_Core::__construct($config);

	list($sig, $data) = explode('|', $_COOKIE[$this->_getCookieKey()], 2);
	if (!$secret = $this->getOption('secret')) {
	    throw new Pix_Exception('you should set the option `secret`');
	}
	if (crc32($data . $secret . $this->_getCookieDomain()) != $sig) {
	    return;
	}

	$data = json_decode($data, true);

	$this->_data = $data;
    }

    protected function _getCookieKey()
    {
	return $this->hasOption('cookie_key') ? $this->getOption('cookie_key') : session_name();
    }

    protected function _getCookiePath()
    {
	return $this->hasOption('cookie_path') ? $this->getOption('cookie_path') : '/';
    }

    protected function _getCookieDomain()
    {
	return $this->hasOption('cookie_domain') ? $this->getOption('cookie_domain') : $_SERVER['SERVER_NAME'];
    }

    protected function _getTimeout()
    {
	return $this->hasOption('timeout') ? $this->getOption('timeout') : null;
    }

    protected function setCookie()
    {
	$data = json_encode($this->_data);
	$sig = crc32($data . $this->getOption('secret') . $this->_getCookieDomain());
	$params = session_get_cookie_params();
	Pix_HttpResponse::setcookie(
	    $this->_getCookieKey(), 
	    $sig . '|' . $data, 
	    $this->_getTimeout() ? (time() + $this->_getTimeout()) : null,
	    $this->_getCookiePath(),
	    $this->_getCookieDomain()
	);
    }

    public function set($key, $value)
    {
	if ($this->_data[$key] !== $value) {
	    $this->_data[$key] = $value;
	    $this->setCookie();
	}
    }

    public function get($key)
    {
	return $this->_data[$key];
    }

    public function delete($key)
    {
	unset($this->_data[$key]);
	$this->setCookie();
    }

    public function clear()
    {
	$this->_data = array();
	$this->setCookie();
    }
}
