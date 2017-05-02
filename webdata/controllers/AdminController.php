<?php

class AdminController extends Pix_Controller
{
    public function init()
    {
        // TODO: admin 應該要再登入一次，最好還意加上 2-step
        if (!$this->user = Hisoku::getLoginUser()) {
            return $this->rediect('/');
        }
        if (!$this->user->isAdmin()) {
            return $this->rediect('/');
        }
        $this->view->user = $this->user;
        $this->view->action = $this->getActionName();
    }

    public function indexAction()
    {
        return $this->redirect('/admin/nodeservers');
    }

    public function nodeserversAction()
    {
    }

    public function databasesAction()
    {
    }

    public function loadbalancersAction()
    {
    }

    public function logAction()
    {
        list(, /*admin*/, /*getlog*/, $category) = explode('/', $this->getURI());
        if (!$category) {
            $category = 'login';
        }
        $this->view->category = $category;
    }

    public function getlogAction()
    {
        list(, /*admin*/, /*getlog*/, $category) = explode('/', $this->getURI());

        if (in_array($category, array('login', 'git-ssh-serve'))) {
            $log_filter = function($line){
                $terms = explode(' ', $line);
                $time = date('c', array_shift($terms));
                return $time . ' ' . implode( ' ' , $terms);
            };
        } else {
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

    public function nodeserverbulkaddportAction()
    {
        list(, /*admin*/, /*nodeserverbulkaddport*/, $ip, $num) = explode('/', $this->getURI());
        if ($_POST['sToken'] != Hisoku::getStoken()) {
            return $this->alert('wrong stoken', '/admin');
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return $this->alert('wrong ip', '/admin');
        }

        $num = intval($_REQUEST['count']);
        if ($num <= 0 or $num > 100) {
            return $this->alert('num must in 1 ~ 100', '/admin');
        }

        for ($i = 1; $i <= $num ; $i ++) {
            if (WebNode::find(array(ip2long($ip), 20000 + $i))) {
                continue;
            }
            try {
                WebNode::initNode($ip, $i);
            } catch (Exception $e) {
                return $this->alert($e->getMessage(), '/admin');
            }
        }

        return $this->alert('done', '/admin');
    }

    public function nodeserveraddportAction()
    {
        list(, /*admin*/, /*nodeserveraddport*/, $ip, $port) = explode('/', $this->getURI());
        if ($_POST['sToken'] != Hisoku::getStoken()) {
            return $this->alert('wrong stoken', '/admin');
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return $this->alert('wrong ip', '/admin');
        }

        $port = intval($port);
        if ($port <= 0 or $port > 100) {
            return $this->alert('port must in 1 ~ 100', '/admin');
        }

        if (WebNode::find(array(ip2long($ip), 20000 + $port))) {
            return $this->alert('port ' . (20000 + $port) . ' is existed', '/admin');
        }

        try {
            WebNode::initNode($ip, $port);
        } catch (Exception $e) {
            return $this->alert($e->getMessage(), '/admin');
        }

        return $this->alert('done', '/admin');
    }

    public function nodeserverdeleteportAction()
    {
        list(, /*admin*/, /*nodeserverdeleteport*/, $ip, $port) = explode('/', $this->getURI());

        if ($_POST['sToken'] != Hisoku::getStoken()) {
            return $this->alert('wrong stoken', '/admin');
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return $this->alert('wrong ip', '/admin');
        }

        $port = intval($port);
        if ($port <= 0 or $port > 100) {
            return $this->alert('port must in 1 ~ 100', '/admin');
        }

        if (!$node = WebNode::find(array(ip2long($ip), 20000 + $port))) {
            return $this->alert('port ' . (20000 + $port) . ' is not found', '/admin');
        }
        $node->delete();

        return $this->alert('done', '/admin');
    }

    public function nodeserverreleaseportAction()
    {
        list(, /*admin*/, /*nodeserverreleaseport*/, $ip, $port) = explode('/', $this->getURI());

        if ($_POST['sToken'] != Hisoku::getStoken()) {
            return $this->alert('wrong stoken', '/admin');
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return $this->alert('wrong ip', '/admin');
        }

        $port = intval($port);
        if ($port <= 0 or $port > 100) {
            return $this->alert('port must in 1 ~ 100', '/admin');
        }

        if (!$node = WebNode::find(array(ip2long($ip), 20000 + $port))) {
            return $this->alert('port ' . (20000 + $port) . ' is not found', '/admin');
        }
        $node->markAsUnused();

        return $this->alert('done', '/admin');
    }

    public function nodeserverstopportAction()
    {
        list(, /*admin*/, /*nodeserverstopport*/, $ip, $port) = explode('/', $this->getURI());

        if ($_POST['sToken'] != Hisoku::getStoken()) {
            return $this->alert('wrong stoken', '/admin');
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return $this->alert('wrong ip', '/admin');
        }

        $port = intval($port);
        if ($port <= 0 or $port > 100) {
            return $this->alert('port must in 1 ~ 100', '/admin');
        }

        if (!$node = WebNode::find(array(ip2long($ip), 20000 + $port))) {
            return $this->alert('port ' . (20000 + $port) . ' is not found', '/admin');
        }
        $node->update(array(
            'project_id' => 0,
            'commit' => '',
            'status' => WebNode::STATUS_STOP,
        ));

        return $this->alert('done', '/admin');
    }

    public function machinesAction()
    {
        if ($machine_id = intval($_GET['machine_id']) and $machine = Machine::find($machine_id)) {
            $this->view->machine = $machine;
        }
    }

    public function addmachineAction()
    {
        if ($_POST['sToken'] != Hisoku::getStoken()) {
            return $this->alert('wrong stoken', '/admin/machines');
        }

        if (!filter_var($_REQUEST['ip'], FILTER_VALIDATE_IP)) {
            return $this->alert('wrong ip', '/admin/machines');
        }

        if ($machine = Machine::find(intval($_GET['machine_id']))) {
            $machine->update(array(
                'ip' => ip2long($_REQUEST['ip']),
            ));
        } else {
            $machine = Machine::insert(array(
                'ip' => ip2long($_REQUEST['ip']),
            ));
        }
        $machine->setGroups($_REQUEST['groups']);

        return $this->alert('add machine done!', '/admin/machines');
    }

    public function searchesAction()
    {
    }

    public function machinelogAction()
    {
        list(, /*admin*/, /*machinelog*/, $machine_id, $time) = explode('/', $this->getURI());
        if (!$status = MachineStatus::find(array(intval($machine_id), intval($time)))) {
            return $this->redirect('/admin/machines');
        }

        $this->view->status = $status;
    }

    public function sslkeyAction()
    {
        if ($_GET['domain']) {
            $this->view->sslkey = SSLKey::find($_GET['domain']);
        }
    }

    public function addsslkeyAction()
    {
        if ($_POST['sToken'] != Hisoku::getStoken()) {
            return $this->alert('wrong stoken', '/admin/sslkey');
        }

        $config = new StdClass;
        $config->ca = $_POST['ca'];
        $config->key = $_POST['key'];
        $config->cert = $_POST['cert'];
        SSLKey::insert(array(
            'domain' => $_POST['domain'],
            'config' => json_encode($config),
        ));
        return $this->alert('add key done!', '/admin/sslkey');
    }

    public function editsslkeyAction()
    {
        if ($_POST['sToken'] != Hisoku::getStoken()) {
            return $this->alert('wrong stoken', '/admin/sslkey');
        }

        if (!$sslkey = SSLKey::find($_GET['domain'])) {
            return $this->alert('wrong stoken', '/admin/sslkey');
        }

        $config = new StdClass;
        $config->ca = array_filter($_POST['ca'], function($a) { return $a; });
        $config->key = $_POST['key'];
        $config->cert = $_POST['cert'];
        $sslkey->update(array(
            'config' => json_encode($config),
        ));
        return $this->alert('update key done!', '/admin/sslkey');
    }
    public function deletesslkeyAction()
    {
        if ($_POST['sToken'] != Hisoku::getStoken()) {
            return $this->alert('wrong stoken', '/admin/sslkey');
        }

        $d = SSLKey::find($_GET['domain']);
        $d->delete();

        return $this->alert('delete done', '/admin/sslkey');

    }

    public function usersAction()
    {
    }
}
