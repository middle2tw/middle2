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
