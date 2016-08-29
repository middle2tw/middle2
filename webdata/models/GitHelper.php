<?php

class GitHelper
{
    public static function system_without_error($command)
    {
        $return_var = 0;
        system($command, $return_var);
        if ($return_var != 0) {
            throw new Exception("Run {$command} failed, code: {$return_var}");
        }
    }

    public static function getGitFileInfo($file_name)
    {
        $ls_tree_cmd = 'git ls-tree HEAD ' . $file_name;
        $ls_result = trim(`$ls_tree_cmd`);
        // Got '100644 blob 433ee4e878d82d375ea2311dcd4f0046a8eb12b6    requirements.txt'
        if (!trim($ls_result)) {
            return null;
        }
        list($perm, $type, $object_id, $file) = preg_split('/\s+/', trim($ls_result));
        $ret = new stdClass;
        $ret->perm = $perm;
        $ret->type = $type;
        $ret->object_id = $object_id;
        $ret->file = $file;
        return $ret;
    }

    /**
     * buildDockerProjectBase 建立這個 project 自己的額外檔案，這個 method 必需以 git 帳號身份執行
     * 
     * @param mixed $project 
     * @access public
     * @return void
     */
    public static function buildDockerProjectBase($project)
    {
        $absolute_path = getenv('HOME') . '/git/' . $project->id . '.git';
        if (!file_exists($absolute_path)) {
            throw new Exception('project not found: ' . $project->name);
        }

        chdir($absolute_path);

        $actions = array();
        foreach (array('requirements.txt', 'Gemfile', 'package.json') as $file) {
            if ($info = self::getGitFileInfo($file)) {
                $actions[] = array(
                    'file' => $file,
                    'info' => $info,
                );
            }
        }

        if (!$actions) { // 沒這些檔案的話什麼都不用動
            return 'default';
        }

        $version = crc32(json_encode($actions));
        $image_id = "{$project->name}-{$version}";

        if (!file_exists("/tmp/project-{$image_id}.tgz")) {
            exec("docker create --name container-{$image_id} middle2 init");
            exec("docker start container-{$image_id}");
            foreach ($actions as $action) {
                $info = $action['info'];
                $tmp_name = tempnam('', '');
                exec("git cat-file -p " . escapeshellarg($info->object_id) . " > " . $tmp_name);

                try {
                    if ($action['file'] == 'requirements.txt') {
                        exec("docker cp {$tmp_name} container-{$image_id}:/requirements.txt");
                        self::system_without_error("docker exec --tty container-{$image_id} pip install --requirement /requirements.txt");
                    } elseif ($action['file'] == 'Gemfile') {
                        exec("docker cp {$tmp_name} container-{$image_id}:/Gemfile");
                        self::system_without_error("docker exec --tty container-{$image_id} gem install bundler");
                        self::system_without_error("docker exec --tty container-{$image_id} bundle install --without development test");
                    } elseif ($action['file'] == 'package.json') {
                        exec("docker cp {$tmp_name} container-{$image_id}:/srv/package.json");
                        self::system_without_error("docker exec --tty container-{$image_id} sh -c \"cd /srv; npm install\"");
                    }
                } catch (Exception $e) {
                    exec("docker stop container-{$image_id}");
                    exec("docker rm container-{$image_id}");
                    throw $e;
                }
                unlink($tmp_name);
            }
            system("php " . __DIR__ . "/../../scripts/docker-export-diff.php container-{$image_id} /tmp/project-{$image_id}.tgz");
            exec("docker stop container-{$image_id}");
            exec("docker rm container-{$image_id}");
        }

        return $version;
    }
}
