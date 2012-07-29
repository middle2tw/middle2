<?php

/**
 * Pix_Controller
 *
 * @package Pix_Controller
 * @version $id$
 * @copyright 2003-2010 PIXNET
 * @author Shang-Rung Wang <srwang@pixnet.tw>
 * @license PHP Version 3.0 {@link http://www.php.net/license/3_0.txt}
 */
class Pix_Controller
{
    static protected $_dispatchers = array();

    public $view = null;
    protected $controllerName = 'index';
    protected $actionName = 'index';

    public function __construct()
    {
	$this->view = new Pix_Partial;
    }

    public function init()
    {
    }

    public function noview()
    {
	throw new Pix_Controller_NoViewException();
    }

    public function setView($v)
    {
	$this->view = $v;
    }

    public function getControllerName()
    {
	return $this->controllerName;
    }

    public function getActionName()
    {
	return $this->actionName;
    }

    public function draw($filename)
    {
	return $this->view->partial($filename, $this->view);
    }

    public function redraw($partial_name)
    {
	echo $this->draw($partial_name);
	return $this->noview();
    }

    public function getURI()
    {
	list($uri, $params) = explode('?', $_SERVER['REQUEST_URI'], 2);
	return $uri;
    }

    public function redirect($url, $code = 302)
    {
	Pix_HttpResponse::redirect($url, $code);
	return $this->noview();
    }

    /**
     * addDispatcher 增加新的 Dispatcher
     *
     * @param Pix_Controller_Dispatcher|callable $dispatcher
     * @static
     * @access public
     * @return void
     */
    public static function addDispatcher($dispatcher)
    {
        if (!($dispatcher instanceof Pix_Controller_Dispatcher ) and !is_callable($dispatcher)) {
            throw new Exception("addDispatcher 只能指定 Pix_Controller_Dispatcher & callable function");
        }
	self::$_dispatchers[] = $dispatcher;
    }

    static protected $_plugins = array();
    static protected $_pluginfuncs = array();

    public function __call($func, $args)
    {
	if (!$plugin = self::$_pluginfuncs[strtolower($func)]) {
	    throw new Pix_Exception("method {$func} is not found");
	}
	if (!$plugin = self::$_plugins[$plugin]) {
	    throw new Pix_Exception("see the ghost");
	}
	array_unshift($args, $this);
        return call_user_func_array(array($plugin, $func), $args);
    }

    public static function addCommonPlugins()
    {
	self::addPlugins('json');
	self::addPlugins('http');
    }

    public static function addPlugins($plugin, array $funcs = array())
    {
	if (!is_scalar($plugin)) {
	    throw new Pix_Exception('plugin name must be string');
	}
	if (@class_exists('Pix_Controller_Plugin_' . ucfirst($plugin))) {
	    $plugin = 'Pix_Controller_Plugin_' . ucfirst($plugin);
	}
	if (!class_exists($plugin)) {
	    throw new Pix_Exception("plugin class '{$plugin}' dose not exist");
	}
	self::$_plugins[$plugin] = $p = new $plugin();
	if (!is_a($p, 'Pix_Controller_Plugin')) {
	    throw new Pix_Exception("class '{$plugin}' is not a Pix_Controller_Plugin");
	}
	if (!$funcs) {
	    $funcs = $p->getFuncs();
	}
	foreach ($funcs as $func) {
	    self::$_pluginfuncs[strtolower($func)] = $plugin;
	}
    }

    public static function dispatch($data_path)
    {
	$baseDir = rtrim($data_path, '/');

	// dispatch
	foreach (self::$_dispatchers as $dispatcher) {
            list($uri, $params) = explode('?', $_SERVER['REQUEST_URI'], 2);
            if (is_callable($dispatcher)) {
                list($controllerName, $actionName, $params) = $dispatcher($uri);
            } elseif ($dispatcher instanceof Pix_Controller_Dispatcher) {
                list($controllerName, $actionName, $params) = $dispatcher->dispatch($uri);
            } else {
                throw new Exception('不明的 Dispatcher');
            }
            if (!is_null($controllerName) and !is_null($actionName)) {
		break;
	    }
	}

        if (is_null($controllerName) or is_null($actionName)) {
	    list($uri, $params) = explode('?', $_SERVER['REQUEST_URI'], 2);
	    $default_dispatcher = new Pix_Controller_Dispatcher_Default();
	    list($controllerName, $actionName, $params) = $default_dispatcher->dispatch($uri);
	}

	try {
            if (is_null($controllerName)) {
		throw new Pix_Controller_Dispatcher_Exception();
	    }

	    $className = ucfirst($controllerName) . 'Controller';
	    $file = $baseDir . '/controllers/' . $className . '.php';
	    if (file_exists($file)) {
		include($file);
	    } else {
		throw new Pix_Controller_Dispatcher_Exception('404 Controller file not found: ' . $file);
	    }

	    if (!class_exists($className)) {
		throw new Pix_Controller_Dispatcher_Exception('404 Class not found');
	    }

	    $controller = new $className();
	    $controller->controllerName = $controllerName;
	    $controller->actionName = $actionName;
	    $controller->view->setPath("$baseDir/views/");
            $controller->init($params);
            if (is_null($controller->actionName)) {
                throw new Pix_Controller_Dispatcher_Exception();
            }

            if (!method_exists($controller, $controller->actionName . 'Action')) {
		throw new Pix_Controller_Dispatcher_Exception('404 Method not found');
	    }
            $controller->{$controller->actionName . 'Action'}($params);

            $file = $controller->view->getPath() . "$controllerName/$controller->actionName.phtml";
	    if (file_exists($file)) {
                echo $controller->draw("$controllerName/$controller->actionName.phtml");
	    } else {
		throw new Pix_Controller_Dispatcher_Exception("404 View file not found!");
	    }
	} catch (Pix_Controller_NoViewException $exception) {
	    // 不顯示 view ，這邊甚麼都不用作　
	} catch (Exception $exception) {
	    // TODO: 導到 ErrorController
	    include($baseDir . '/controllers/ErrorController.php');
	    $controller = new ErrorController();
	    $controller->view->setPath("$baseDir/views/");
	    $controller->view->exception = $exception;
	    try {
		$controller->init($params);
		$controller->errorAction($params);
		$file = $controller->view->getPath() . "error/error.phtml";
		if (file_exists($file)) {
		    echo $controller->draw("error/error.phtml");
		}
	    } catch (Pix_Controller_NoViewException $exception) {
		// 不顯示 view ，這邊什麼都不用作
	    }
	}
    }
}
