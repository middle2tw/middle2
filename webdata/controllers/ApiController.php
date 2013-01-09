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

        $c = new Pix_Cache;
        $c->inc('Project:access_count:' . $project->id);
        $c->set('Project:access_at:' . $project->id, time());

        // find current
        $nodes = WebNode::search(array(
            'project_id' => $project->id,
            'status' => WebNode::STATUS_WEBNODE,
            'commit' => $project->commit,
        ));

        // TODO: find STATUS_WEBPROCESSING

        if (count($nodes)) {
            $ret = new StdClass;
            $ret->error = false;
            $ret->nodes = array();
            
            foreach ($nodes as $node) {
                $c->inc("WebNode:access_count:{$node->ip}:{$node->port}");
                $c->set("WebNode:access_at:{$node->ip}:{$node->port}", time());

                $ret->nodes[] = array(long2ip($node->ip), $node->port);
            }
            return $this->json($ret);
        }

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

            $random_node->update(array(
                'project_id' => $project->id,
                'commit' => $project->commit,
                'status' => WebNode::STATUS_WEBPROCESSING,
            ));

            $node_id = $random_node->port - 20000;
            $ip = long2ip($random_node->ip);

            $session = ssh2_connect($ip, 22);
            ssh2_auth_pubkey_file($session, 'root', WEB_PUBLIC_KEYFILE, WEB_KEYFILE);
            ssh2_exec($session, "clone {$project->name} {$node_id}");

            $session = ssh2_connect($ip, 22);
            ssh2_auth_pubkey_file($session, 'root', WEB_PUBLIC_KEYFILE, WEB_KEYFILE);
            ssh2_exec($session, "restart-web {$project->name} {$node_id}");

            $random_node->update(array(
                'status' => WebNode::STATUS_WEBNODE,
            ));
    
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
