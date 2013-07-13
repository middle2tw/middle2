<?php

class ApiController extends Pix_Controller
{
    public function init()
    {
    }

    public function weblogAction()
    {
        // TODO: 要限內網並且加上 secret key
        if (!$logs = json_decode($_POST['data'])) {
            throw new Exception('invalid data');
        }

        $messages = array();
        foreach ($logs as $log) {
            $messages[] = array(
                'category' => "app-{$log->project}-error",
                'message' => $log->time . ' ' . $log->id . ' ' . urlencode($log->log),
            );
        }
        Logger::log($messages);
        return $this->json(1);
    }

    public function updatemachinestatusAction()
    {
        // TODO: 要判斷只有內網可以
        $name = strval($_GET['name']);
        $status = $_POST['status'];

        MachineStatus::insert(array(
            'name' => $name,
            'status' => $status,
            'updated_at' => time(),
        ));

        return $this->json(1);
    }

    public function getnodesAction()
    {
        $request_host = strval($_GET['domain']);
        $request_port = intval($_GET['port']);

        $project = null;

        if (preg_match('#(.*)' . preg_quote(USER_DOMAIN, '#') . '#', $request_host, $matches)) {
            $project = Project::find_by_name($matches[1]);
        } elseif ($domain = CustomDomain::find($request_host)) {
            $project = $domain->project;
        } else {
            return $this->json(array(
                'error' => true,
                'message' => 'Unknown domain: ' . $request_host,
            ));
        }

        if (is_null($project)) {
            return $this->json(array(
                'error' => true,
                'message' => 'Unknown domain: ' . $request_host,
            ));
        }

        if ($project->isProcessingWebNode()) {
            return $this->json(array(
                'error' => false,
                'wait' => true,
            ));
        }

        $c = new Pix_Cache;
        $c->inc('Project:access_count:' . $project->id);
        $c->set('Project:access_at:' . $project->id, time());

        $ret = new StdClass;
        $ret->project = $project->name;
        try {
            $nodes = $project->getWebNodes();

            $ret->error = false;
            $ret->nodes = array();
            foreach ($nodes as $node) {
                $c->inc("WebNode:access_count:{$node->ip}:{$node->port}");
                $c->set("WebNode:access_at:{$node->ip}:{$node->port}", time());

                $ret->nodes[] = array(long2ip($node->ip), $node->port);
            }
        } catch (Exception $e) {
            $ret->error = true;
            $ret->message = $e->getMessage();
        }
        return $this->json($ret);
    }
}
