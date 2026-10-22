<?php
// Release web nodes still running a project image older than its rebuilt one,
// one node at a time, oldest start_at first. Same effect as the Release button
// on /admin/nodeservers (WebNode::markAsUnused()).
//
// usage: php release-old-nodes.php <image-times.tsv> [--run]
//   image-times.tsv: "<project name>\t<new image created unix time>" per line
//   without --run only prints the plan

include(__DIR__ . '/../../webdata/init.inc.php');

define('MAX_LOAD', 4);
define('START_TIMEOUT', 330); // start-web.sh waits up to 300s for the app
define('QUEUE_TIMEOUT', 600);
define('MIN_UNUSED', 8);
define('MAX_OVER', 3);

function logline($message)
{
    echo date('H:i:s') . ' ' . $message . "\n";
}

function waitForLoad()
{
    while (($load = sys_getloadavg()[0]) >= MAX_LOAD) {
        logline("load {$load}, waiting");
        sleep(30);
    }
}

function nodeGroup($node)
{
    return strval(json_decode($node->config)->{'node-group'});
}

// keep enough unused nodes in the group for new requests; released nodes
// stay OVER until cron/1min/updatenodes.php resets them
function waitForFreeNodes($group)
{
    while (true) {
        $unused = 0;
        $over = 0;
        foreach (WebNode::search(1) as $n) {
            if (nodeGroup($n) !== $group) {
                continue;
            }
            if ($n->status == WebNode::STATUS_UNUSED) {
                $unused ++;
            } elseif ($n->status == WebNode::STATUS_OVER) {
                $over ++;
            }
        }
        if ($unused >= MIN_UNUSED and $over <= MAX_OVER) {
            return;
        }
        logline("group '{$group}' unused {$unused}, over {$over}, waiting");
        sleep(20);
    }
}

// load balancer starts new nodes one at a time, don't add to its queue
function waitForQueue()
{
    $start = time();
    while (count(WebNode::search(array('status' => WebNode::STATUS_WEBPROCESSING)))) {
        if (time() - $start > QUEUE_TIMEOUT) {
            throw new Exception('some node is still in WebNode processing after ' . QUEUE_TIMEOUT . 's');
        }
        sleep(5);
    }
}

// if the project got a new node after release, wait until it is ready
function waitForRestart($project, $released_at)
{
    $start = time();
    while (true) {
        $processing = count(WebNode::search(array('project_id' => $project->id, 'status' => WebNode::STATUS_WEBPROCESSING)));
        if (!$processing) {
            $new_nodes = WebNode::search(array('project_id' => $project->id, 'status' => WebNode::STATUS_WEBNODE))->search("start_at >= {$released_at}");
            return count($new_nodes) ? 'restarted' : 'idle';
        }
        if (time() - $start > START_TIMEOUT) {
            throw new Exception("{$project->name} is still in WebNode processing after " . START_TIMEOUT . 's');
        }
        sleep(5);
    }
}

list(, $image_times_file) = $argv;
$run = in_array('--run', $argv);

$image_times = array();
foreach (file($image_times_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    list($name, $time) = explode("\t", $line);
    $image_times[$name] = intval($time);
}

$targets = array();
foreach (WebNode::search(1) as $node) {
    if (!in_array($node->status, array(WebNode::STATUS_WEBNODE, WebNode::STATUS_WAIT))) {
        continue;
    }
    if (!$node->project or !array_key_exists($node->project->name, $image_times)) {
        continue;
    }
    if ($node->start_at >= $image_times[$node->project->name]) {
        continue;
    }
    $targets[] = $node;
}
// Wait nodes are kept for cron and serve no web traffic, release them first
usort($targets, function($a, $b) {
    $a_wait = ($a->status == WebNode::STATUS_WAIT) ? 0 : 1;
    $b_wait = ($b->status == WebNode::STATUS_WAIT) ? 0 : 1;
    return ($a_wait - $b_wait) ?: ($a->start_at - $b->start_at);
});

foreach ($targets as $i => $node) {
    printf("%2d %-15s %5d %-30s %-11s start %s access %s\n", $i + 1, long2ip($node->ip), $node->port, $node->project->name,
        $node->status == WebNode::STATUS_WAIT ? 'Wait' : 'WebNode', date('m/d H:i', $node->start_at), date('m/d H:i:s', $node->getAccessAt()));
}
echo count($targets) . " nodes to release\n";
if (!$run) {
    exit;
}

foreach ($targets as $i => $node) {
    waitForLoad();
    $is_wait = ($node->status == WebNode::STATUS_WAIT);
    if (!$is_wait) {
        waitForFreeNodes(nodeGroup($node));
        waitForQueue();
    }

    // re-read, the node may have changed while we were waiting
    $node = WebNode::find(array($node->ip, $node->port));
    if (!$node or !$node->project or !array_key_exists($node->project->name, $image_times)
        or !in_array($node->status, array(WebNode::STATUS_WEBNODE, WebNode::STATUS_WAIT))
        or $node->start_at >= $image_times[$node->project->name]) {
        logline('[' . ($i + 1) . '] skip, node changed: ' . long2ip($targets[$i]->ip) . ':' . $targets[$i]->port);
        continue;
    }

    $project = $node->project;
    $released_at = time();
    $node->markAsUnused('rebuild on patched middle2 base image (CVE-2026-109618, CVE-2026-109619)');
    logline('[' . ($i + 1) . '/' . count($targets) . "] released " . long2ip($node->ip) . ":{$node->port} {$project->name}");

    if ($is_wait) {
        continue;
    }

    // give the load balancer a moment to pick up a request for this project
    sleep(15);
    logline("  {$project->name}: " . waitForRestart($project, $released_at));
}
logline('DONE');
