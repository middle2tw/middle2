<?php

include(__DIR__ . '/../webdata/init.inc.php');

$view = Hisoku::getView();
echo $view->partial('index/index.phtml');
