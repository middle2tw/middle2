<?php

// restart docker registry if ssl key changed
//
// Usage: php update-docker-registry-ssl.php {le_docker_cert_dir} {docker_cert_dir}

$le_docker_cert_dir = $_SERVER['argv'][1];
$docker_cert_dir = $_SERVER['argv'][2];


if (!file_Exists("{$le_docker_cert_dir}/privkey.pem")) {
    throw new Exception("{$le_docker_cert_dir}/privkey.pem not found");
}
if (!file_Exists("{$le_docker_cert_dir}/privkey.pem")) {
    throw new Exception("{$le_docker_cert_dir}/privkey.pem not found");
}

if (file_get_contents("{$docker_cert_dir}/privkey.pem") == file_get_contents("{$le_docker_cert_dir}/privkey.pem")) {
    error_log("not change");
    exit;
}

foreach (array('cert', 'fullchain', 'privkey') as $f) {
    copy("{$le_docker_cert_dir}/{$f}.pem", "{$docker_cert_dir}/{$f}.pem");
}

system("docker restart registry");
