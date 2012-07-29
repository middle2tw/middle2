<?php

/**
 * Pix_Session_Core 
 * 
 * @abstract
 * @package Pix_Session
 * @version $id$
 * @copyright 2003-2010 PIXNET
 * @author Shang-Rung Wang <srwang@pixnet.tw> 
 * @license PHP Version 3.0 {@link http://www.php.net/license/3_0.txt}
 */
abstract class Pix_Session_Core
{
    public static function loadCore($core, $config)
    {
	if (class_exists('Pix_Session_Core_' . ucfirst($core))) {
	    $core = 'Pix_Session_Core_' . $core;
	}

	if (!class_exists($core)) {
	    throw new Pix_Exception("core $core not found");
	}

	return new $core($config);
    }

    abstract public function set($key, $value);
    abstract public function get($key);
    abstract public function delete($key);
    abstract public function clear();

    protected $_options = array();

    public function __construct($options = array())
    {
	$this->_options = $options;
    }

    public function getOption($key, $options = array())
    {
	if (isset($options[$key])) {
	    return $options[$key];
	}
	if (isset($this->_options[$key])) {
	    return $this->_options[$key];
	}
	return Pix_Session::getOption($key);
    }

    public function hasOption($key)
    {
	return isset($this->_options[$key]);
    }

    public function setOption($key, $value)
    {
	$this->_options[$key] = $value;
    }
}
