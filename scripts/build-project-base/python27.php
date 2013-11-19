#!/usr/bin/env php
<?php
// 每次 post-commit 之後，將 requirements.txt 內的東西先抓好
// 包成一個 tar gz ，之後就解開 tar gz 就好了，速度超快!
// 
// 每次 post-commit 時
// 1. 先找 /srv/chroot/1000~1999 之間是否有可以用的 chroot
// 2. 找到後，在 /srv/chroot/1xxx.lock 加上 lock 避免衝突
// 3. 把 /srv/code/images/dev-pythonXX.tar.gz 解壓縮進 chroot 中
// 4. 把所有檔案的修改日期都改成 2000/01/01 ，以方便作完 pip 可以找出新增加的檔
// 5. 把需要的 requirements.txt 搬到 /srv/chroot/1xxx/ 下面
// 6. pip install 裝進 chroot 內
// 7. 找出新裝的東西，搬出來放進 tar gz 中，完工
//
// 之後要 clone 的時候
// 1. 把 tar gz 解開就好了
//
// 用法  php python-prebuild.php [requirements.txt file]
//
define('TEMPLATE', 'python27');

class Prebuilder
{
    public function error($message)
    {
        die($message);
    }

    public function findAvailableRootAndLock()
    {
        $shuffled_roots = glob('/srv/chroot/1???');
        shuffle($shuffled_roots);
        foreach ($shuffled_roots as $root) {
            $lock_file = "{$root}.lock";

            if (file_exists("{$root}.initing") or file_exists("{$root}.used")) {
                continue;
            }

            $fp = fopen($lock_file, 'w+');
            if (!flock($fp, LOCK_EX | LOCK_NB)) {
                continue;
            }

            $this->fp = $fp;
            return $root;
        }

        return $this->error("No avaiabled root");
    }

    public function main($req_file, $force_rebuild = false)
    {
        if (0 !== posix_getuid()) {
            return $this->error("Muse be root");
        }

        if (!file_exists($req_file)) {
            return $this->error("File not found");
        }

        $project_file = '/tmp/project-' . TEMPLATE . '-' . md5_file($req_file);

        // 已經有了，不需要再做了
        if (!$force_rebuild and file_exists($project_file . '.tar.gz')) {
            return;
        }

        if (file_exists($project_file . '.tar.gz')) {
            unlink($project_file . '.tar.gz');
        }

        // 先找可以用的 chroot
        error_log('find available root...');
        $root = $this->findAvailableRootAndLock();

        error_log('untar python.tar.gz...');
        // 把 python package 解進去
        system("tar zxf /srv/code/images/lang-" . TEMPLATE . ".tar.gz --directory={$root}");

        error_log('find diff file...');
        // 把新解進去的檔案都改成 2001/1/1
        system("find {$root} -printf '%TY%Tm%Td,,,%p\n' | grep -v '^20000101' | awk -F,,, '{print \"\\\"\"$2\"\\\"\"}' | xargs -n 100 touch --no-dereference --date=20000101");

        error_log('pip installing ...');
        // 安裝 python package
        copy($req_file, $root . "/requirements.txt");
        $cmd = "chroot {$root} /usr/local/bin/pip --log /srv/logs/pip.log install --requirement /requirements.txt";
        $fp = proc_open($cmd, array(0 => array('pipe', 'r'), 1 => array('pipe' , 'w'), 2 => array('pipe', 'w')), $pipes, NULL, array('PYTHONUNBUFFERED' => 'x'));

        while (false !== ($line = fgets($pipes[1], 4096))) {
            if (feof($pipes[1])) {
                break;
            }

            if ($line === '') {
                continue;
            }

            if (0 === strpos($line, 'Downloading/unpacking')) {
                echo trim(str_replace($req_file, 'requirements.txt', $line)) . "\n";
            }
        }
        $status = proc_get_status($fp);
        if ($status['exitcode'] != 0) {
            // TODO: 要 log 起來錯誤原因參考
            return $this->error('build failed');
        }
        proc_close($fp);

        unlink($root . '/requirements.txt');
        system("rm -rf {$root}/tmp");

        // 把檔案弄進去
        chdir($root);

        error_log('build tar.gz...');
        // TODO: 處理過程中會不會處理到一半的檔案被人拿走...
        // 處理檔案
        system("find . -type f -printf \"%TY%Tm%Td %p\n\" | grep -v '^20000101' | awk '{print $2}' | xargs -n 100 tar -uf " . escapeshellarg($project_file . '.tar'));
        // 處理 symbolic link
        system("find . -type l -printf \"%TY%Tm%Td %p\n\" | grep -v '^20000101' | awk '{print $2}' | xargs -n 100 tar -uf " . escapeshellarg($project_file . '.tar'));

        system("gzip --force {$project_file}.tar");

        // 標示為已用完，並解除 lock
        touch("{$root}.used");

        flock($this->fp, LOCK_UN);    // release the lock
        fclose($this->fp);
        unlink("{$root}.lock");
    }
}

$p = new Prebuilder;
$p->main($_SERVER['argv'][1], array_key_exists(2, $_SERVER['argv']) ? true : false);
