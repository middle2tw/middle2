#!/usr/bin/env php
<?php
// 每次 post-commit 之後，將 requirements.txt 內的東西先抓好
// 讓之後跑起服務時可以節省多一點時間
// 
// 每次 post-commit 時
// 1. 先用 pip install --no-install -r requirements.txt
//    將東西下載到 /tmp/pip-build 下
//    (PS: 這邊可以用 download cache)
// 2. 把 /tmp/pip-build 備份到其他地方
//
// 之後要 clone 的時候
// 1. 先把其他地方備份的內容搬到 /tmp/pip-build
// 2. 用 pip install --no-download -r requirements.txt 安裝
//
// 用法  php python-downloader.php [requirements.txt file]

list(, $req_file) = $_SERVER['argv'];

$download_cache_path = '/tmp/pip-download-cache';
$build_path = '/tmp/pip-build-' . md5_file($req_file) . '/';
$build_file = '/tmp/pip-' . md5_file($req_file) . '.tar.gz';

if (file_exists($build_file)) {
    exit;
}

$command = 'pip install --no-install'
    . ' --force-reinstall'
    . ' --upgrade'
    . ' --requirement ' . escapeshellarg($req_file)
    . ' --build ' . escapeshellarg($build_path)
    . ' --download-cache ' . escapeshellarg($download_cache_path);

$fp = proc_open($command, array(1 => array('pipe', 'w')), $pipes);

while (false !== ($line = fgets($pipes[1], 1024))) {
    if (preg_match('#^Downloading/unpacking#', $line)) {
        echo str_replace(realpath($req_file), 'requirements.txt', $line);
    }
}
fclose($pipes[1]);

chdir($build_path);
system("tar zcf " . escapeshellarg($build_file) . ' .');
system("rm -rf " . escapeshellarg($build_path));
