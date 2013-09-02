#!/usr/bin/env php
<?php
include(__DIR__ . '/../../webdata/init.inc.php');

$pv_count = $size_count = array();
foreach (glob("/srv/logs/scribed/app-*") as $log_directory) {
    if (!preg_match('#/srv/logs/scribed/app-([^-]*-[^-]*-[0-9]*)$#', $log_directory, $matches)) {
        continue;
    }
    $app_name = $matches[1];

    foreach (glob("{$log_directory}/app-{$app_name}-" . date('Y-m-d', time() - 86400) . '_*') as $log_file) {
        $fp = fopen($log_file, 'r');
        while ($line = fgets($fp)) {
            list($hostname_ip_none_none_datetime_timezone, $method_uri_version, $rescode_size, $referer) = explode('"', $line);
            list($rescode, $size) = explode(' ', $rescode_size);
            $pv_count[$app_name] ++;
            $size_count[$app_name] += $size;
            
        }
        fclose($fp);
    }
}

$node_count = array();
$today = mktime(0, 0, 0);
$yesterday = $today - 86400;
foreach (glob("/srv/logs/scribed/app-*-node") as $log_directory) {
    if (!preg_match('#/srv/logs/scribed/app-([^-]*-[^-]*-[0-9]*)-node$#', $log_directory, $matches)) {
        continue;
    }
    $app_name = $matches[1];

    // 從昨天到今天
    foreach (array($yesterday, $today) as $day) {
        foreach (glob("{$log_directory}/app-{$app_name}-node-" . date('Y-m-d', $day) . '_*') as $log_file) {
            $fp = fopen($log_file, 'r');
            while ($line = fgets($fp)) {
                $data = json_decode($line);
                $ip_port = $data->ip . '-' . $data->port;
                if ($data->status == 'over' or $data->status == 'wait') {
                    if ($day == $yesterday) { // 昨天的檢查看看是不是有跨天，有的話就要只從零點算起
                        $node_count[$app_name][$data->type] += min($data->spent, $data->time - $yesterday);
                    } else { // 今天的話就要看看是不是跨天的
                        $node_count[$app_name][$data->type] += min(86400, max(0, $today - ($data->time - $data->spent)));
                    }
                }
            }
            fclose($fp);
        }
    }
}

foreach (WebNode::search(1) as $node) {
    if ($node->start_at > $today) {
        continue;
    }

    if (WebNode::STATUS_CRONNODE == $node->status) {
        $node_count[$node->project->name]['cron'] += max(0, $today - max($node->start_at, $yesterday));
    } elseif (WebNode::STATUS_WEBNODE == $node->status) {
        $node_count[$node->project->name]['web'] += max(0, $today - max($node->start_at, $yesterday));
    }
}


$output = fopen('/srv/logs/nodes-count/' . date('Ymd', $yesterday), 'w');
foreach (array_unique(array_merge(array_keys($pv_count), array_keys($size_count), array_keys($node_count))) as $app_name) {
    fputcsv($output, array(
        $app_name,
        Project::find_by_name($app_name)->getEAV('note'),
        $pv_count[$app_name],
        $size_count[$app_name],
        floor($node_count[$app_name]['web']),
        floor($node_count[$app_name]['cron']),
    ));
}
fclose($output);
