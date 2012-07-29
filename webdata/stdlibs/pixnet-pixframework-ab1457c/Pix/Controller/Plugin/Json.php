<?php

/**
 * Pix_Controller_Plugin_Json 
 * 
 * @uses Pix
 * @uses _Controller_Plugin
 * @package Pix_Controller
 * @version $id$
 * @copyright 2003-2010 PIXNET
 * @author Shang-Rung Wang <srwang@pixnet.tw> 
 * @license PHP Version 3.0 {@link http://www.php.net/license/3_0.txt}
 */
class Pix_Controller_Plugin_Json extends Pix_Controller_Plugin
{
    public function getFuncs()
    {
	return array('isJson', 'json', 'jsonp');
    }

    public function isJson($controller)
    {
	return preg_match('#application/json#', $_SERVER['HTTP_ACCEPT']);
    }

    public function json($controller, $obj)
    {
	header('Content-Type: application/json');
	echo @json_encode($obj);
	return $controller->noview();
    }

    public function jsonp($controller, $obj, $callback)
    {
	header('Content-Type: application/javascript');
	if (!preg_match('/^[a-zA-Z0-9_]+$/', strval($callback))) {
	    return $controller->json($obj);
	}
	echo $callback . '(' . @json_encode($obj) . ')';
	return $controller->noview();
    }
}
