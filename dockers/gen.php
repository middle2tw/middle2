<?php

include(__DIR__ . '/../webdata/init.inc.php');

if (file_exists(__DIR__ .'/generated-config')) {
    system('rm -rf ' . __DIR__ . '/generated-config');
}

mkdir(__DIR__ . '/generated-config');

// add loadbalancer ip for apache proxy

mkdir(__DIR__ . '/generated-config/etc/apache2/sites-enabled/', 0777, true);
$content = '';
foreach (Hisoku::getLoadBalancers() as $ip) {
    $content .= "RemoteIPTrustedProxy {$ip}/32\n";
}
file_put_contents(__DIR__ . '/generated-config/etc/apache2/sites-enabled/loadbalancer.conf', $content);
