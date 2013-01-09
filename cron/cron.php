#!/usr/bin/env php
<?php

$type = $_SERVER['argv'][1];

foreach (glob(__DIR__ . '/' . $type . '/*') as $cronfile) {
    system($cronfile);
}
