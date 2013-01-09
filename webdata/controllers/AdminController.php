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
        $node->update(array(
            'project_id' => 0,
            'commit' => '',
            'status' => WebNode::STATUS_UNUSED,
        ));

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
}
