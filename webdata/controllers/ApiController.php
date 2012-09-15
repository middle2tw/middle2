<?php

class ApiController extends Pix_Controller
{
    public function init()
    {
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

        // find current
        $nodes = WebNode::search(array('project_id' => $project->id, 'commit' => $project->commit));
        if (count($nodes)) {
            $ret = new StdClass;
            $ret->error = false;
            $ret->nodes = array();
            
            foreach ($nodes as $node) {
                // TODO: move to background.. update access time
                $node->update(array('access_at' => time()));
                $ret->nodes[] = array(long2ip($node->ip), $node->port);
            }
            return $this->json($ret);
        }

        // TODO: move to background.. release unused nodes
        WebNode::search(array('project_id' => $project_id))->update(array('project_id' => 0));
        $time_5min = time() - 300;
        WebNode::search("`access_at` AND `access_at` < {$time_5min}")->update(array('access_at' => 0, 'project_id' => 0));

        // TODO: check deploying

        $choosed_nodes = array();
        while (true) {
            $free_nodes_count = count(WebNode::search(array('project_id' => 0)));
            if (!$free_nodes_count) {
                // TODO; log it
                return $this->json(array(
                    'error' => true,
                    'message' => 'No free nodes',
                ));
            }

            if (!$random_node = WebNode::search(array('project_id' => 0))->offset(rand(0, $free_nodes_count - 1))->first()) {
                continue;
            }

            $random_node->update(array('project_id' => $project->id, 'commit' => $project->commit));

            $node_id = $random_node->port - 20000;
            $ip = long2ip($random_node->ip);

            $session = ssh2_connect($ip, 22);
            ssh2_auth_pubkey_file($session, 'deploy', '/srv/config/web-key.pub', '/srv/config/web-key');
            ssh2_exec($session, "clone {$project->name} {$node_id}");

            $session = ssh2_connect($ip, 22);
            ssh2_auth_pubkey_file($session, 'root', '/srv/config/web-key.pub', '/srv/config/web-key');
            ssh2_exec($session, "restart-php-fpm {$node_id}");
    
            $choosed_nodes[] = $random_node;

            if (count($choosed_nodes) >= 1) {
                break;
            }
        }

        $ret = new StdClass;
        $ret->error = false;
        $ret->nodes = array();
        foreach ($choosed_nodes as $node) {
            $ret->nodes[] = array(long2ip($node->ip), $node->port);
        }
        return $this->json($ret);
    }
}
