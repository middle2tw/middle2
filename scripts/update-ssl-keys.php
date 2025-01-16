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
    if (strpos($domain, '#') === 0) {
        continue;
    }

    if (!is_dir("certs/{$domain}")) {
        throw new Exception("domain {$domain} not found");
    }

    if (preg_match('#cert-(.*).pem#', readlink("certs/{$domain}/cert.pem"), $matches)) {
        $time = max($time, $matches[1]);
    }

    $cert_pattern = '/-----BEGIN CERTIFICATE-----[^-]+-----END CERTIFICATE-----/s';
    $key_pattern = '/-----BEGIN RSA PRIVATE KEY-----[^-]+-----END RSA PRIVATE KEY-----/s';

    $config = new StdClass;
    preg_match_all($cert_pattern, trim(file_get_contents("certs/{$domain}/fullchain.pem")), $matches);
    $config->ca = $matches[0];
    preg_match($cert_pattern, trim(file_get_contents("certs/{$domain}/cert.pem")), $matches);
    $config->cert = $matches[0];
    $config->key = trim(file_get_contents("certs/{$domain}/privkey.pem"));
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

$fp = fopen('dns-domains.txt', 'r');
while ($domains = fgets($fp)) {
    $domains = explode(' ', trim($domains));
    $first_domain = $domains[0];
    foreach ($domains as $domain) {
        if (!$domain = trim($domain)) {
            continue;
        }

        if (!is_dir("certs/{$first_domain}")) {
            throw new Exception("domain {$first_domain} not found");
        }

        if (preg_match('#cert-(.*).pem#', readlink("certs/{$first_domain}/cert.pem"), $matches)) {
            $time = max($time, $matches[1]);
        }

        $cert_pattern = '/-----BEGIN CERTIFICATE-----[^-]+-----END CERTIFICATE-----/s';

        $config = new StdClass;
        preg_match_all($cert_pattern, trim(file_get_contents("certs/{$first_domain}/fullchain.pem")), $matches);
        $config->ca = $matches[0];
        preg_match($cert_pattern, trim(file_get_contents("certs/{$first_domain}/cert.pem")), $matches);
        $config->cert = $matches[0];
        $config->key = trim(file_get_contents("certs/{$first_domain}/privkey.pem"));
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
}

if ($time != trim(file_get_contents('/tmp/le_version'))) {
    error_log('restart web server');
    system("systemctl reload m2-lb.service");
    file_put_contents('/tmp/le_version', $time);
}
