<?php

class ProjectController extends Pix_Controller
{
    public function init()
    {
        if (!$this->user = Hisoku::getLoginUser()) {
            return $this->rediect('/');
        }
        $this->view->user = $this->user;

        if (getenv('TRY_MODE')) {
            $this->try_mode = getenv('TRY_MODE');
            $this->view->try_mode = $this->try_mode;
        }
    }

    public function detailAction()
    {
        list(, /*project*/, /*detail*/, $name) = explode('/', $this->getURI());

        if (preg_match('#^\d+$#', $name) and $this->user->isAdmin()) {
            $project = Project::find($name);
            return $this->redirect('/project/detail/' . $project->name);
        }
        if (!$project = Project::find_by_name($name)) {
            return $this->alert('Project not found', '/');
        }

        if (!$project->isMember($this->user) and !$this->user->isAdmin()) {
            return $this->alert('Project not found', '/');
        }

        $enable_addons = array(
            'mysql' => count(Machine::getMachinesByGroup('mysql')),
            'pgsql' => count(Machine::getMachinesByGroup('pgsql')),
            'search' => count(Machine::getMachinesByGroup('search')),
        );
        $this->view->project = $project;
        $this->view->enable_addons = $enable_addons;
    }

    public function deletedomainAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: log it
            return $this->alert('error', '/');
        }

        list(, /*project*/, /*adddomain*/, $name) = explode('/', $this->getURI());
        if (!$project = Project::find_by_name($name)) {
            return $this->alert('Project not found', '/');
        }

        if (!$project->isMember($this->user)) {
            return $this->alert('Project not found', '/');
        }

        if (!$domain = $project->custom_domains->search(array('domain' => $_GET['domain']))->first()) {
            return $this->alert('domain not found', '/');
        }

        $domain->delete();
        WebNode::cleanLoadBalancerCache(); // 更改 domain 後要清空 lb cache
        return $this->redirect('/project/detail/' . $project->name);
    }

    public function adddomainAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: log it
            return $this->alert('error', '/');
        }

        if ($this->try_mode) {
            return $this->alert('目前為試用模式，此功能暫不開放', '/');
        }

        list(, /*project*/, /*adddomain*/, $name) = explode('/', $this->getURI());
        if (!$project = Project::find_by_name($name)) {
            return $this->alert('Project not found', '/');
        }

        if (!$project->isMember($this->user)) {
            return $this->alert('Project not found', '/');
        }

        // from http://regexlib.com/REDetails.aspx?regexp_id=306
        if (!preg_match('#^(([\w][\w\-\.]*)\.)?([\w][\w\-]+)(\.([\w][\w\.]*))?$#', $_POST['domain'])) {
            return $this->alert('Invalid domain', "/project/detail/{$project->name}");
        }

        if (stripos($_POST['domain'], getenv('APP_SUFFIX'))) {
            return $this->alert('Invalid domain', "/project/detail/{$project->name}");
        }

        try {
            $project->custom_domains->insert(array(
                'domain' => strval($_POST['domain']),
            ));
        } catch (Pix_Table_DuplicateException $e) {
            return $this->alert('duplicate domain: ' . $_POST['domain'], "/project/detail/{$project->name}");
        }

        WebNode::cleanLoadBalancerCache(); // 更改 domain 後要清空 lb cache

        return $this->redirect('/project/detail/' . $project->name);
    }

    public function deletevariableAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: log it
            return $this->alert('error', '/');
        }

        list(, /*project*/, /*addvariable*/, $name, $key) = explode('/', $this->getURI());
        $key = urldecode($key);
        if (!$project = Project::find_by_name($name)) {
            return $this->alert('Project not found', '/');
        }

        if (!$project->isMember($this->user)) {
            return $this->alert('Project not found', '/');
        }

        if (!$variable = $project->variables->search(array('key' => $key))->first()) {
            return $this->alert('variable not found', '/');
        }

        $variable->delete();
        return $this->redirect('/project/detail/' . $project->name);
    }

    public function addvariableAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: log it
            return $this->alert('error', '/');
        }

        list(, /*project*/, /*addvariable*/, $name) = explode('/', $this->getURI());
        if (!$project = Project::find_by_name($name)) {
            return $this->alert('Project not found', '/');
        }

        if (!$project->isMember($this->user)) {
            return $this->alert('Project not found', '/');
        }

        if ($_POST['file-config']) {
            $key = 'file:' . time();
            $value = strval($_POST['filename']) . "\n" . strval($_POST['value']);
        } else {
            $key = strval($_POST['key']);
            $value = strval($_POST['value']);
        }

        // TODO: check valid key & value
        $project->variables->insert(array(
            'key' => $key,
            'value' => $value,
            'is_magic_value' => 0,
        ));

        return $this->redirect('/project/detail/' . $project->name);
    }

    public function editnoteAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: log it
            return $this->alert('error', '/');
        }

        list(, /*project*/, /*editnote*/, $name, $key) = explode('/', $this->getURI());
        if (!$project = Project::find_by_name($name)) {
            return $this->alert('Project not found', '/');
        }

        if (!$project->isMember($this->user)) {
            return $this->alert('Project not found', '/');
        }

        $project->setEAV('note', strval($_POST['note']));
        return $this->redirect('/project/detail/' . $project->name);
    }

    public function edittemplateAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: log it
            return $this->alert('error', '/');
        }

        list(, /*project*/, /*edittemplate*/, $name, $key) = explode('/', $this->getURI());
        if (!$project = Project::find_by_name($name)) {
            return $this->alert('Project not found', '/');
        }

        if (!$project->isMember($this->user)) {
            return $this->alert('Project not found', '/');
        }

        $templates = Project::getTemplates();
        if (!array_key_exists($_POST['template'], $templates)) {
            return $this->error('template not found', '/');
        }

        $project->setEAV('template', strval($_POST['template']));
        return $this->redirect('/project/detail/' . $project->name);
    }

    public function addelasticaddonAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: log it
            return $this->alert('error', '/');
        }

        if (!Machine::getMachinesByGroup('search')->count()) {
            return $this->alert('目前未支援 ElasticSearch', '/');
        }

        list(, /*project*/, /*addelasticaddon*/, $name, $addon_id) = explode('/', $this->getURI());
        if (!$project = Project::find_by_name($name)) {
            return $this->alert('Project not found', '/');
        }

        if (!$project->isMember($this->user)) {
            return $this->alert('Project not found', '/');
        }

        $key = is_scalar($_POST['key']) ? $_POST['key'] : 'SEARCH_URL';

        if ($addon_id) {
            if (!$addon = Addon_Elastic::find(intval($addon_id))) {
                return $this->alert('Addon not found', '/');
            }
            $addon->saveProjectVariable($key);
        } else {
            Addon_Elastic::addDB($project, $key);
        }

        return $this->redirect('/project/detail/' . $project->name);
    }

    public function addpgsqladdonAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: log it
            return $this->alert('error', '/');
        }

        if (!Machine::getMachinesByGroup('pgsql')->count()) {
            return $this->alert('目前未支援 PgSQL', '/');
        }

        list(, /*project*/, /*addpgsqladdon*/, $name, $addon_id) = explode('/', $this->getURI());
        if (!$project = Project::find_by_name($name)) {
            return $this->alert('Project not found', '/');
        }

        if (!$project->isMember($this->user)) {
            return $this->alert('Project not found', '/');
        }

        $key = is_scalar($_POST['key']) ? $_POST['key'] : 'DATABASE_URL';

        if ($addon_id) {
            if (!$addon = Addon_PgSQLDB::find(intval($addon_id))) {
                return $this->alert('Addon not found', '/');
            }
            if (!$addon_member = $addon->members->search(array('project_id' => $project->id))->first()) {
                return $this->alert('Addon not found', '/');
            }
            $addon_member->saveProjectVariable($key);
        } else {
            Addon_PgSQLDB::addDB($project, $key);
        }

        return $this->redirect('/project/detail/' . $project->name);
    }

    public function addmysqladdonAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: log it
            return $this->alert('error', '/');
        }

        if (!Machine::getMachinesByGroup('mysql')->count()) {
            return $this->alert('目前未支援 MySQL', '/');
        }

        list(, /*project*/, /*addmysqladdon*/, $name, $addon_id) = explode('/', $this->getURI());
        if (!$project = Project::find_by_name($name)) {
            return $this->alert('Project not found', '/');
        }

        if (!$project->isMember($this->user)) {
            return $this->alert('Project not found', '/');
        }

        $key = is_scalar($_POST['key']) ? $_POST['key'] : 'DATABASE_URL';

        if ($addon_id) {
            if (!$addon = Addon_MySQLDB::find(intval($addon_id))) {
                return $this->alert('Addon not found', '/');
            }
            if (!$addon_member = $addon->members->search(array('project_id' => $project->id))->first()) {
                return $this->alert('Addon not found', '/');
            }
            $addon_member->saveProjectVariable($key);
        } else {
            Addon_MySQLDB::addDB($project, $key);
        }

        return $this->redirect('/project/detail/' . $project->name);
    }

    public function addmemcacheaddonAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: log it
            return $this->alert('error', '/');
        }

        list(, /*project*/, /*addmemcacheaddon*/, $name) = explode('/', $this->getURI());
        if (!$project = Project::find_by_name($name)) {
            return $this->alert('Project not found', '/');
        }

        if (!$project->isMember($this->user)) {
            return $this->alert('Project not found', '/');
        }

        Addon_Memcached::addDB($project);

        return $this->redirect('/project/detail/' . $project->name);
    }
    public function editvariableAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: log it
            return $this->alert('error', '/');
        }

        list(, /*project*/, /*editvariable*/, $name, $key) = explode('/', $this->getURI());
        $key = urldecode($key);
        if (!$project = Project::find_by_name($name)) {
            return $this->alert('Project not found', '/');
        }

        if (!$project->isMember($this->user)) {
            return $this->alert('Project not found', '/');
        }

        if (!$variable = $project->variables->search(array('key' => $key))->first()) {
            return $this->alert('Variable not found', '/');
        }

        if ($_POST['file-config']) {
            $value = strval($_POST['filename']) . "\n" . strval($_POST['value']);
        } else {
            $value = strval($_POST['value']);
        }
        $variable->update(array(
            'value' => strval($value),
            'is_magic_value' => 0,
        ));
        return $this->redirect('/project/detail/' . $project->name);
    }

    public function addcronjobAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: log it
            return $this->alert('error', '/');
        }

        if ($this->try_mode) {
            return $this->alert('目前為試用模式，此功能暫不開放', '/');
        }

        list(, /*project*/, /*addcronjob*/, $name) = explode('/', $this->getURI());
        if (!$project = Project::find_by_name($name)) {
            return $this->alert('Project not found', '/');
        }

        if (!$project->isMember($this->user)) {
            return $this->alert('Project not found', '/');
        }

        $project->cronjobs->insert(array(
            'job' => strval($_POST['job']),
            'period' => intval($_POST['period']),
        ));

        return $this->redirect('/project/detail/' . $project->name);
    }

    public function deletecronjobAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: log it
            return $this->alert('error', '/');
        }

        list(, /*project*/, /*deletecronjob*/, $name, $id) = explode('/', $this->getURI());
        if (!$project = Project::find_by_name($name)) {
            return $this->alert('Project not found', '/');
        }

        if (!$project->isMember($this->user)) {
            return $this->alert('Project not found', '/');
        }

        if (!$cronjob = $project->cronjobs->search(array('id' => $id))->first()) {
            return $this->alert('Cronjob not found', '/');
        }

        $cronjob->delete();
        return $this->redirect('/project/detail/' . $project->name);
    }

    public function cronlogAction()
    {
        list(, /*project*/, /*cronlog*/, $name) = explode('/', $this->getURI());
        if (!$project = Project::find_by_name($name)) {
            return $this->alert('Project not found', '/');
        }
        $this->view->project = $project;

        if (!$project->isMember($this->user)) {
            return $this->alert('Project not found', '/');
        }
    }

    public function editcronjobAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: log it
            return $this->alert('error', '/');
        }

        list(, /*project*/, /*editcronjob*/, $name, $id) = explode('/', $this->getURI());
        if (!$project = Project::find_by_name($name)) {
            return $this->alert('Project not found', '/');
        }

        if (!$project->isMember($this->user)) {
            return $this->alert('Project not found', '/');
        }

        if (!$cronjob = $project->cronjobs->search(array('id' => $id))->first()) {
            return $this->alert('Cronjob not found', '/');
        }

        $cronjob->update(array(
            'job' => strval($_POST['job']),
            'period' => intval($_POST['period']),
        ));
        return $this->redirect('/project/detail/' . $project->name);
    }

    public function deletememberAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: log it
            return $this->alert('error', '/');
        }

        list(, /*project*/, /*deletemember*/, $name) = explode('/', $this->getURI());
        if (!$project = Project::find_by_name($name)) {
            return $this->alert('Project not found', '/');
        }

        if (!$project->isAdmin($this->user)) {
            return $this->alert('You are not admin', '/');
        }

        if (!$user = User::find_by_name(strval($_GET['account']))) {
            return $this->alert('User not found', '/');
        }

        if (!$project_member = $project->members->search(array('user_id' => $user->id))->first()) {
            return $this->alert('project member not found', '/');
        }

        if ($project_member->is_admin and count($project->members->search(array('is_admin' => 1))) == 1) {
            return $this->alert('there is only one admin', '/');
        }

        $project_member->delete();
        return $this->redirect('/project/detail/' . $project->name);
    }

    public function addmemberAction()
    {
        if (Hisoku::getStoken() != $_POST['sToken']) {
            // TODO: log it
            return $this->alert('error', '/');
        }

        list(, /*project*/, /*addmember*/, $name) = explode('/', $this->getURI());
        if (!$project = Project::find_by_name($name)) {
            return $this->alert('Project not found', '/');
        }

        if (!$project->isAdmin($this->user)) {
            return $this->alert('You are not admin', '/');
        }

        if (!$user = User::find_by_name(strval($_POST['account']))) {
            return $this->alert('User not found', '/');
        }

        try {
            $project->members->insert(array(
                'user_id' => $user->id,
            ));
        } catch (Pix_Table_DuplicateException $e) {
        }

        return $this->redirect('/project/detail/' . $project->name);
    }

    public function getlogAction()
    {
        list(, /*project*/, /*getlog*/, $name, $type) = explode('/', $this->getURI());
        if (!$project = Project::find_by_name($name)) {
            return $this->alert('Project not found', '/');
        }

        if (!$project->isMember($this->user) and !$this->user->isAdmin()) {
            return $this->alert('Project not found', '/');
        }

        if ($type == 'error') {
            $category = "app-{$name}-error";
            $log_filter = function($line){
                list($timestamp, $node_id, $log) = explode(' ', $line);
                return date('c', $timestamp) . ' [' . $node_id . ']' . urldecode($log);
            };
        } elseif ('node' == $type) {
            $category = "app-{$name}-node";
            $log_filter = function($line){
                $data = json_decode($line);
                if ('start' == $data->status) {
                    $message = "Start a {$data->type} node";
                    if ($data->type == 'cron') {
                        $message .= ': ' . $data->command;
                    }
                } elseif ('over' == $data->status or 'wait' == $data->status) {
                    $message = "Stop the {$data->type} node, spent: {$data->spent}";
                }
                $message .= ' ' . json_encode($data);

                return date('c', $data->time) . "[{$data->ip}-{$data->port}] {$message}";
            };
        } else {
            $category = "app-{$name}";
            $log_filter = null;
        }

        if ($_GET['before']) {
            list($file, $cursor) = explode(",", $_GET['before']);
            $logs = Logger::getLog($category, array('cursor-before' => array('file' => $file, 'cursor' => $cursor)));
        } elseif ($_GET['after']) {
            list($file, $cursor) = explode(",", $_GET['after']);
            $logs = Logger::getLog($category, array('cursor-after' => array('file' => $file, 'cursor' => $cursor)));
        } else {
            $logs = Logger::GetLog($category);
        }

        if (!is_null($log_filter)) {
            for ($i = 0; $i < count($logs[0]); $i ++) {
                $logs[0][$i] = $log_filter($logs[0][$i]);
            }
        }
        return $this->json($logs);
    }
}
