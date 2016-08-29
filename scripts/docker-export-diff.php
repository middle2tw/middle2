<?php

$container = $_SERVER['argv'][1];
$output = $_SERVER['argv'][2];

$diff = `docker diff $container`;

$changes = array();
$inserts = array();

foreach (explode("\n", trim($diff)) as $line) {
    list($type, $path) = explode(' ', $line, 2);
    $path_terms = explode('/', $path);

    $inserted = false;
    for ($i = 1; $i < count($path_terms); $i ++) {
        $parted_path = implode('/', array_slice($path_terms, 0, $i));
        if (array_key_exists($parted_path, $changes)) {
            $changes[$parted_path]['type'] = 'dir';
        }

        if (array_key_exists($parted_path, $inserts)) {
            // 如果他上層資料夾已經在 inserts 中，就不需要再額外處理了
            $inserted = true;
            $inserts[$parted_path]['type'] = 'dir';
            $inserts[$parted_path]['children'] ++;
        }
    }

    if ($inserted) {
        continue;
    }

    if ($type == 'C') { // 如果是檔案要抓出來，資料夾就跳過
        $changes[$path] = array(
            'type' => 'file', // 先猜他是檔案，如果下面有子檔案才會改成資料夾
        );
    } elseif ($type == 'A') { // 新增
        $inserts[$path] = array(
            'type' => 'file',
            'children' => 0,
        );
    } else {
        // TODO: 
        throw new Exception("還未支援 {$type} 種類");
    }
}

foreach ($changes as $path => $info) {
    if ($info['type'] == 'file') {
        // TODO: change dir 可以忽略不管但是假如遇到 change file 可能要處理
        throw new Exception("遇到 {$path} 被修改，需要支援");
    }
}

$export_dir = tempnam('/tmp/', 'docker-export');
unlink($export_dir);
if (!file_exists($export_dir)) {
    mkdir($export_dir);
}
foreach ($inserts as $path => $info) {
    if ($info['type'] == 'file') {
        if (!file_exists($export_dir . dirname($path))) {
            mkdir($export_dir . dirname($path), 0777, true);
        }
    } elseif ($info['type'] == 'dir') {
        if (!file_exists($export_dir . $path)) {
            mkdir($export_dir . $path, 0777, true);
        }
    }

    system("docker cp {$container}:{$path} {$export_dir}" . dirname($path));
}

system("cd {$export_dir}; tar zcf {$output} *");
system("rm -rf {$export_dir}");
