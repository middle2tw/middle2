#!/usr/bin/env php
<?php
include(__DIR__ . '/../../webdata/init.inc.php');

Pix_Table::getDefaultDb()->query("DELETE FROM machine_status WHERE updated_at < " . time() . " - 7 * 86400");
Pix_Table::getDefaultDb()->query("OPTIMIZE TABLE machine_status");
