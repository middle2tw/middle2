<?php

include(__DIR__ . '/../webdata/init.inc.php');

Pix_Session::setCore('cookie', array('secret' => getenv('SESSION_SECRET')));
Pix_Controller::dispatch(__DIR__ . '/../webdata/');
