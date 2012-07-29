<?php

class ErrorController extends Pix_Controller
{
    public function errorAction()
    {
        echo var_dump($this->view->exception);
        exit;
    }
}
