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

    public static function getGitFileInfo($file_name, $branch = 'HEAD')
    {
        $ls_tree_cmd = "git ls-tree {$branch} " . $file_name;
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

    public static function getGitFileContent($file_name, $branch = 'HEAD') {
        $show_cmd = "git show {$branch}:{$file_name}";
        $show_result = `$show_cmd`;
        return $show_result;
    }

    /**
     * buildDockerProjectBase 建立這個 project 自己的額外檔案，這個 method 必需以 git 帳號身份執行
     * 
     * @param mixed $project 
     * @access public
     * @return void
     */
    public static function buildDockerProjectBase($project, $branch = 'HEAD', $clean_build = false)
    {
        $absolute_path = getenv('HOME') . '/git/' . $project->id . '.git';
        if (!file_exists($absolute_path)) {
            throw new Exception('project not found: ' . $project->name);
        }

        chdir($absolute_path);

        $rev_parse_cmd = "git rev-parse {$branch}";
        $commit_id = trim(`$rev_parse_cmd`);

        $apt_packages = explode("\n", self::getGitFileContent('Aptfile', $branch));
        if ('' == trim(implode('', $apt_packages))) {
            $apt_packages = array();
        }

        $actions = array();
        foreach (array('requirements.txt', 'Gemfile', 'package.json') as $file) {
            if ($info = self::getGitFileInfo($file, $branch)) {
                $actions[] = array(
                    'file' => $file,
                    'info' => $info,
                );
            }
        }

        // pull from remote image
        $docker_registry = getenv('DOCKER_REGISTRY');

        // check container is exists
        $cmd = "docker inspect container-{$project->name}";
        $obj = json_decode(`$cmd`)[0];
        if ($obj) {
            self::system_without_error("docker rm -f container-{$project->name}");
        }

        try {
            if ($clean_build) {
                throw new Exception("clean build");
            }
            self::system_without_error("docker --config /srv/config/docker pull {$docker_registry}/image-{$project->name}");
            $cmd = "docker inspect {$docker_registry}/image-{$project->name}";
            $obj = json_decode(`$cmd`)[0];
            if ($obj->Comment == $commit_id) {
                return $commit_id;
            }

            self::system_without_error("docker create --name container-{$project->name} {$docker_registry}/image-{$project->name} init");

        } catch (Exception $e) {
            // image is not on remote
            self::system_without_error("docker create --name container-{$project->name} middle2 init");
        }

        self::system_without_error("docker start container-{$project->name}");
        self::system_without_error("docker exec container-{$project->name} mkdir -p /srv/web");
        self::system_without_error("docker exec container-{$project->name} find /srv/web/ -not -path  '/srv/web/node_modules/*' -not -path '/srv/web/node_modules' -not -path '/srv/web/' -delete");
        self::system_without_error("git archive --format=tar {$branch}| docker exec -i container-{$project->name} tar -xf - -C /srv/web/");

        if (count($apt_packages)) {
            $apt_install_cmd = "apt-get update -y";
            self::system_without_error("docker exec --tty container-{$project->name} {$apt_install_cmd}");
            $apt_install_cmd = "apt-get upgrade -y";
            self::system_without_error("docker exec --tty container-{$project->name} {$apt_install_cmd}");
            $apt_install_cmd = "apt-get install -y " . implode(' ', $apt_packages);
            self::system_without_error("docker exec --tty container-{$project->name} {$apt_install_cmd}");
        }

        $config = json_decode($project->config) ?: new StdClass;
        if (
            (property_exists($config, 'no-build') or !$config->{'no-build'}) and
            self::getGitFileContent('m2-build.sh', $branch)
        ) {
            self::system_without_error("docker exec --tty container-{$project->name} env -i PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin sh -c 'cd /srv/web; ./m2-build.sh'");
        }

        foreach ($actions as $action) {
            $info = $action['info'];

            try {
                if ($action['file'] == 'requirements.txt') {
                    self::system_without_error("docker exec --tty container-{$project->name} pip install --requirement /srv/web/requirements.txt");
                } elseif ($action['file'] == 'Gemfile') {
                    self::system_without_error("docker exec --tty container-{$project->name} sh -c 'cd /srv/web; gem install bundler'");
                    self::system_without_error("docker exec --tty container-{$project->name} sh -c 'cd /srv/web; bundle install --without development test'");
                } elseif ($action['file'] == 'package.json') {
                    self::system_without_error("docker exec --tty container-{$project->name} env -i PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin sh -c 'cd /srv/web; npm install --unsafe-perm'");
                }
            } catch (Exception $e) {
                self::system_without_error("docker stop container-{$project->name}");
                //self::system_without_error("docker rm container-{$project->name}");
                throw $e;
            }
        }

        self::system_without_error("docker stop container-{$project->name}");
        self::system_without_error("docker commit --message {$commit_id} container-{$project->name} {$docker_registry}/image-{$project->name}");
        self::system_without_error("docker --config /srv/config/docker push {$docker_registry}/image-{$project->name}");
        self::system_without_error("docker rm container-{$project->name}");

        return $commit_id;
    }
}
