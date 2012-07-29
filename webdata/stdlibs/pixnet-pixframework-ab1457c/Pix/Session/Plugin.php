<?php

/**
 * Pix_Session_Plugin 
 * 
 * @abstract
 * @package Pix_Session
 * @version $id$
 * @copyright 2003-2010 PIXNET
 * @author Shang-Rung Wang <srwang@pixnet.tw> 
 * @license PHP Version 3.0 {@link http://www.php.net/license/3_0.txt}
 */
abstract class Pix_Session_Plugin
{
	abstract public function listMethod();

	protected $_options;

	public function __construct($options)
	{
		$this->_options = $options;
	}

	public function getOption($key)
	{
		return $this->_options[$key];
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
