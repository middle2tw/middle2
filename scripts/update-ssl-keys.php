<?php

include(__DIR__ . '/../webdata/init.inc.php');

if (is_dir($_SERVER['argv'][1])) {
    chdir($_SERVER['argv'][1]);
}

$fp = fopen('domains.txt', 'r');
$time = 0;
while ($domain = fgets($fp)) {
    if (!$domain = trim($domain)) {
        continue;
    }

    if (!is_dir("certs/{$domain}")) {
        throw new Exception("domain {$domain} not found");
    }

    if (preg_match('#cert-(.*).pem#', readlink("certs/{$domain}/cert.pem"), $matches)) {
        $time = max($time, $matches[1]);
    }

    $config = new StdClass;
    $config->ca = trim(file_get_contents("certs/{$domain}/fullchain.pem"));
    $config->ca = str_replace("-----END CERTIFICATE-----\n-----BEGIN CERTIFICATE-----\n", "-----END CERTIFICATE-----\n----------\n----------\n-----BEGIN CERTIFICATE-----\n", $config->ca);
    $config->ca = explode("----------\n----------\n", $config->ca);
    $config->key = trim(file_get_contents("certs/{$domain}/privkey.pem"));
    $config->cert = trim(file_get_contents("certs/{$domain}/cert.pem"));
    if ($k = SSLKey::find($domain)) {
        $k->update(array(
            'config' => json_encode($config),
        ));
    } else {
        SSLKey::insert(array(
            'domain' => $domain,
            'config' => json_encode($config),
        ));
    }
}

if ($time != trim(file_get_contents('/tmp/le_version'))) {
    error_log('restart web server');
    system("kill `cat /tmp/middle2.pid`");
    file_put_contents('/tmp/le_version', $time);
}
