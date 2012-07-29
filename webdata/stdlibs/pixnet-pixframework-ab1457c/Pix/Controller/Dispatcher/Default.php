<?php

/**
 * Pix_Controller_Dispatcher_Default
 *
 * @uses Pix
 * @uses _Controller_Dispatcher
 * @package Pix_Controller
 * @version $id$
 * @copyright 2003-2010 PIXNET
 * @author Shang-Rung Wang <srwang@pixnet.tw>
 * @license PHP Version 3.0 {@link http://www.php.net/license/3_0.txt}
 */
class Pix_Controller_Dispatcher_Default extends Pix_Controller_Dispatcher
{
    public function dispatch($url)
    {
	list(, $controllerName, $actionName) = explode(DIRECTORY_SEPARATOR, $url);
	list($actionName, $ext) = explode('.', $actionName);
	$args = array();
        if ($ext) {
	    $args['ext'] = $ext;
        }

	$actionName = $actionName ? $actionName : 'index';
	$controllerName = $controllerName ? $controllerName : 'index';

        if (!preg_match('/^([A-Za-z]{1,})$/' , $controllerName)) {
            return null;
        }
        if (!preg_match('/^([A-Za-z][A-Za-z0-9]*)$/' , $actionName)) {
            return array($controllerName, null);
        }
	return array($controllerName, $actionName, $args);
    }
}
