<?php

/**
 * Pix_Controller_Plugin 
 * 
 * @abstract
 * @package Pix_Controller
 * @version $id$
 * @copyright 2003-2010 PIXNET
 * @author Shang-Rung Wang <srwang@pixnet.tw> 
 * @license PHP Version 3.0 {@link http://www.php.net/license/3_0.txt}
 */
abstract class Pix_Controller_Plugin
{
    /**
     * getFuncs 這個 Plugin 支援哪些 function
     * 
     * @abstract
     * @access public
     * @return void
     */
    abstract public function getFuncs();

    /**
     * init 再呼叫所有 function 前會執行的
     * 
     * @access public
     * @return void
     */
    public function init($controller)
    {
    }
}
