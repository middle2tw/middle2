<?php

include(__DIR__ . '/../webdata/init.inc.php');

Pix_Session::setAdapter('cookie', array('secret' => getenv('SESSION_SECRET'), 'cookie_key' => 'HISOKU_SESSION'));
Pix_Controller::addCommonHelpers();
Pix_Controller::dispatch(__DIR__ . '/../webdata/');
