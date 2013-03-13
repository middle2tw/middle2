<?php

$url = $_SERVER['REQUEST_URI'];
$hostname = parse_url($url, PHP_URL_HOST);
$path = parse_url($url, PHP_URL_PATH);

$prefix = '/tmp/pypi-files/';
$cache_file = $prefix . $hostname . '/' . md5($url) . '-' . basename($path);
if (file_exists($cache_file)) {
    header("Content-Length: " . filesize($cache_file));
    readfile($cache_file);
    exit;
}

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$content = curl_exec($curl);
$info = curl_getinfo($curl);
curl_close($curl);
if (in_array($info['http_code'], array(301, 302))) {
    header('Location: ' . $info['redirect_url'], true, $info['http_code']);
    exit;
}

if (200 != $info['http_code']) {
    http_response_code($info['http_code']);
    exit;
}

if (!file_exists(dirname($cache_file))) {
    mkdir(dirname($cache_file), 0777, true);
}
file_put_contents($cache_file, $content);
file_put_contents($prefix . '/logs', $cache_file . ' ' . $url . "\n", FILE_APPEND);
readfile($cache_file);
