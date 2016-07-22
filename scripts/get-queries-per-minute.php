<?php

$log_file = '/tmp/hisoku-query-counter';

$now = time();
$curl = curl_init('https://middle2.com');
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Host: healthcheck'));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$content = curl_exec($curl);
$obj = json_decode($content);

if (!file_exists($log_file)) {
    file_put_contents($log_file, "{$now} {$obj->request_serial}");
    echo 0;
    exit;
}

list($prev_time, $prev_serial) = explode(' ', file_get_contents($log_file));
echo max(0, ($obj->request_serial - $prev_serial) / ($now - $prev_time));
file_put_contents($log_file, "{$now} {$obj->request_serial}");
