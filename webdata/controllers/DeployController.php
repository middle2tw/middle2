<?php

class DeployController extends Pix_Controller
{
    public function init()
    {
        if (!$this->user = Hisoku::getLoginUser()) {
            return $this->redirect('/');
        }
        $this->view->user = $this->user;

        if (getenv('TRY_MODE')) {
            $this->try_mode = getenv('TRY_MODE');
            $this->view->try_mode = $this->try_mode;
            $this->project_limit = 3;
            $this->view->project_limit = $this->project_limit;
        }
    }

    public function indexAction()
    {
        if (!$_GET['template']) {
            return $this->redirect('/');
        }

        if (!preg_match('/^https?:\/\/github\.com\/(?P<user>[^\/]+)\/(?P<repo>[^\/]+)(\.git)?$/U', $_GET['template'], $matches)) {
           return $this->alert("template must be GitHub web URL", '/');
        }

        $curl = curl_init("https://raw.githubusercontent.com/{$matches['user']}/{$matches['repo']}/master/app.json");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($curl);
        curl_close($curl);

        if (!$app_json = json_decode($content)) {
            return $this->alert("app.json parse error", '/');
        }

        // TODO: check app.json

        if ($this->try_mode) {
            $project_count = Project::search(array('created_by' => $this->user->id))->count();
            if (intval($project_count) >= $this->project_limit) {
                return $this->alert('目前為試用模式，project 數量上限為 ' . $this->project_limit, '/');
            }
        }

        try {
            $project = $this->user->addProject();
        } catch (InvalidException $e) {
            // TODO: error
            return $this->redirect('/');
        } catch (Pix_Table_DuplicateException $e) {
            // TODO: error
            return $this->redirect('/');
        }

        $project->setEAV('note', strval($app_json->name));

        $prepare_addons = array();
        if ($app_json->addons) {
           foreach ($app_json->addons as $addon) {
               if ("string" === gettype($addon)) {
                   $prepare_addons[$addon] = 'DATABASE_URL';
               } else if ("object" === gettype($addon)) {
                   $prepare_addons[$addon->plan] = $addon->as ? $addon->as : 'DATABASE_URL';
               }
           }
        }

        foreach ($prepare_addons as $key => $value) {
            switch (strtolower($key)) {
                case 'mysql':
                    Addon_MySQLDB::addDB($project, $value);
                    break;
                case 'pgsql':
                case 'postgresql':
                case 'heroku-postgresql':
                    Addon_PgSQLDB::addDB($project, $value);
                    break;
                default:
            }
        }

        if ($app_json->env) {
            foreach (get_object_vars($app_json->env) as $key => $obj) {
                if ($obj->value && strlen($obj->value) > 0) {
                    $project->variables->insert(array(
                        'key' => $key,
                        'value' => $obj->value,
                        'is_magic_value' => 0,
                    ));
                }
            }
        }
        
        $from = 'https://github.com/' . $matches['user'] . '/' . $matches['repo'];

        $ip = GIT_SERVER;
        $session = ssh2_connect($ip, 22);
        ssh2_auth_pubkey_file($session, 'git', WEB_PUBLIC_KEYFILE, WEB_KEYFILE);
        $stream = ssh2_exec($session, "import-project-repo " . $project->name . " " . $from);
        stream_set_blocking($stream, true);
        $ret = stream_get_contents($stream);

        if ($app_json->success_url) {
            return $this->redirect("http://" . $project->name . getenv('APP_SUFFIX') . $app_json->success_url);
        }

        return $this->redirect("https://" . getenv('MAINPAGE_DOMAIN') . "/project/detail/" . $project->name);
    }
}
