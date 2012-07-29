<?php

/**
 * Pix_Session 
 * 
 * @package Pix_Session
 * @version $id$
 * @copyright 2003-2010 PIXNET
 * @author Shang-Rung Wang <srwang@pixnet.tw> 
 * @license PHP Version 3.0 {@link http://www.php.net/license/3_0.txt}
 */
class Pix_Session
{
    protected static $_obj = null;
    protected static $_core = 'default';
    protected static $_core_options = array();

    public static function setCore($core, $options = array())
    {
	self::$_core = $core;
	self::$_core_options = $options;
    }

    public function __construct()
    {
	self::getObject();
    }

    protected static function getObject()
    {
	if (!is_null(self::$_obj)) {
	    return self::$_obj;
	}
	return self::$_obj = Pix_Session_Core::loadCore(self::$_core, self::$_core_options);
    }

    protected static $_plugin_class = array();
    protected static $_plugin_func = array();
    protected static $_plugin_obj = array();
    protected static $_plugin_options = array();
    protected static $_plugin_id = 1;

    public static function __callStatic($name, $args)
    {
	return self::__call($name, $args);
    }

    public function __call($name, $args)
    {
	if (!$plugin_id = self::$_plugin_func[strtolower($name)]) {
	    throw new Pix_Exception("method `{$name}` is not found");
	}

	if (!$obj = self::$_plugin_obj[$plugin_id]) {
	    $class = self::$_plugin_class[$plugin_id];
	    $obj = self::$_plugin_obj[$plugin_id] = new $class(self::$_plugin_options[$plugin_id]);
	}

	return call_user_func_array(array($obj, strtolower($name)), $args);
    }

    public static function addPlugin($plugin, $funcs = null, $options = array())
    {
	if (@class_exists('Pix_Session_Plugin_' . ucfirst($plugin))) {
	    $plugin = 'Pix_Session_Plugin_' . ucfirst($plugin);
	}

	if (!class_exists($plugin)) {
	    throw Pix_Exception("plugin {$plugin} not found");
	}

	if (!is_subclass_of($plugin, Pix_Session_Plugin)) {
	    throw new Pix_Exception("{$plugin} must be a Pix_Session_Plugin");
	}

	if (is_null($funcs)) {
	    $funcs = call_user_func(array($plugin, 'listMethod'));
	}

	foreach ($funcs as $func) {
	    self::$_plugin_func[strtolower($func)] = self::$_plugin_id;
	}
	self::$_plugin_options[self::$_plugin_id] = $options;
	self::$_plugin_class[self::$_plugin_id] = $plugin;
	self::$_plugin_id ++;
    }

    public static function get($key)
    {
	$obj = self::getObject();
	return $obj->get($key);
    }

    public static function set($key, $value)
    {
	$obj = self::getObject();
	return $obj->set($key, $value);
    }

    public static function delete($key)
    {
	$obj = self::getObject();
	return $obj->delete($key);
    }

    public static function clear()
    {
	$obj = self::getObject();
	return $obj->clear();
    }

    public static function setOption($key, $value)
    {
	$obj = self::getObject();
	return $obj->setOption($key, $value);
    }

    public static function getOption($key)
    {
	$obj = self::getObject();
	return $obj->getOption($key);
    }

    public function __get($key)
    {
	return self::get($key);
    }

    public function __set($key, $value)
    {
	return self::set($key, $value);
    }

    public function __unset($key)
    {
	return self::delete($key);
    }
}
