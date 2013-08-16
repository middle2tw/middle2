<?php

include(__DIR__ . '/../webdata/init.inc.php');

class FirewallGenerator
{
    public function getBaseRules()
    {
        return array(
            '#!/bin/sh',
            'iptables -F',
            'iptables -X',
            'iptables -Z',
            'iptables -P INPUT DROP',
            'iptables -P OUTPUT ACCEPT',
            'iptables -P FORWARD ACCEPT',
            'iptables -A INPUT -i lo -j ACCEPT',
            'iptables -A INPUT -m state --state RELATED,ESTABLISHED -j ACCEPT',
        );
    }

    public function testSuffix()
    {
        return array(
            'sleep 30',
            'iptables -F',
            'iptables -X',
            'iptables -Z',
            'iptables -P INPUT ACCEPT',
            'iptables -P OUTPUT ACCEPT',
            'iptables -P FORWARD ACCEPT',
        );
    }

    protected $_server_categories = array();
    protected $_category_servers = array();

    protected function _addServer($ip, $category)
    {
        if (!$this->_category_servers[$category]) {
            $this->_category_servers[$category] = array();
        }
        $this->_category_servers[$category][$ip] = $ip;

        if (!$this->_server_categories[$ip]) {
            $this->_server_categories[$ip] = array();
        }
        $this->_server_categories[$ip][$category] = $category;
    }

    public function initServers()
    {
        $dev_servers = Hisoku::getDevServers();
        $scribe_servers = $dev_servers;
        $mainpage_servers = $dev_servers;
        $git_servers = $dev_servers;
        $private_memcache_servers = $dev_servers;
        $nfs_servers = $dev_servers;

        $this->_server_categories = $this->_category_servers = array();
        // dev server
        foreach ($dev_servers as $ip) {
            $this->_addServer($ip, 'dev');
        }

        // load balancers
        foreach (Hisoku::getLoadBalancers() as $ip) {
            $this->_addServer($ip, 'loadbalancer');
        }

        // mysql
        foreach (Hisoku::getMySQLServers() as $ip) {
            $this->_addServer($ip, 'mysql');
        }

        // pgsql
        foreach (Hisoku::getPgSQLServers() as $ip) {
            $this->_addServer($ip, 'pgsql');
        }

        // node servers
        foreach (Hisoku::getNodeServers() as $ip) {
            $this->_addServer($ip, 'node');
        }

        // scribe server
        foreach ($scribe_servers as $ip) {
            $this->_addServer($ip, 'scribe');
        }

        foreach (Hisoku::getSearchServers() as $ip) {
            $this->_addServer($ip, 'elastic_search');
        }

        // mainpage server
        foreach ($mainpage_servers as $ip) {
            $this->_addServer($ip, 'mainpage');
        }

        // git server
        foreach ($git_servers as $ip) {
            $this->_addServer($ip, 'git');
        }

        // private memcache server
        foreach ($private_memcache_servers as $ip) {
            $this->_addServer($ip, 'private_memcache');
        }

        foreach ($nfs_servers as $ip) {
            $this->_addServer($ip, 'nfs');
        }
    }

    public function getAllowRules()
    {
        return array(
            'node' => array(
                array('20001:29999', array('loadbalancer')),
                array('22', array('mainpage', 'loadbalancer')),
            ),
            'elastic_search' => array(
                array('9200', array('node', 'mainpage')),
            ),
            'mainpage' => array(
                array('9999', array('loadbalancer')),
            ),
            'loadbalancer' => array(
                array('80', array('PUBLIC')),
                array('443', array('PUBLIC')),
            ),
            'git' => array(
                array('22', array('PUBLIC')),
            ),
            'private_memcache' => array(
                array('11211', array('loadbalancer', 'mainpage')),
            ),
            'mysql' => array(
                array('3306', array('loadbalancer', 'mainpage', 'node')),
            ),
            'pgsql' => array(
                array('5432', array('loadbalancer', 'mainpage', 'node', 'PUBLIC')),
            ),
            'scribe' => array(
                array('1426', array('loadbalancer', 'node')),
            ),
            'dev' => array(
                array('22', array('PUBLIC')), // 以後要用 VPN 把這個 rule 拿掉
                array('5566', array('PUBLIC')), // 以後要用 VPN 把這個 rule 拿掉
            ),
            'nfs' => array(
                array('111', array('node')),
                array('u111', array('node')),
                array('2049', array('node')),
                array('u2049', array('node')),
                array('32764:32769', array('node')),
                array('u32764:32769', array('node')),
            ),
            'ALL' => array(
                array('22', array('dev')),
            ),
        );
    }

    public function getIPsFromCategories($categories)
    {
        $ips = array();
        foreach ($categories as $category) {
            $ips = array_merge($ips, $this->_category_servers[$category]);
        }
        return array_unique($ips);
    }

    public function main()
    {
        $this->initServers();
        $allow_rules = $this->getAllowRules();

        foreach ($this->_server_categories as $ip => $categories) {
            $rules = $this->getBaseRules();

            $match_rules = array();
            $match_rule_categories = array();
            // 先把 ALL 放進來
            foreach ($allow_rules['ALL'] as $rule) {
                $match_rules[$rule[0]] = $rule[1];
                $match_rule_categories[$rule[0]] = array('ALL');
            }

            // 把分類符合的塞進來
            foreach ($categories as $category) {
                if (!array_key_exists($category, $allow_rules)) {
                    error_log('category ' . $category . ' is not found');
                    continue;
                }
                foreach ($allow_rules[$category] as $rule) {
                    if (!$match_rules[$rule[0]]) {
                        $match_rules[$rule[0]] = array();
                        $match_rule_categories[$rule[0]] = array();
                    }
                    $match_rules[$rule[0]] = array_unique(array_merge($match_rules[$rule[0]], $rule[1]));
                    $match_rule_categories[$rule[0]][] = $category;
                }
            }

            foreach ($match_rules as $port => $categories) {
                $protocol = 'tcp';
                if (preg_match('#^u(.*)#', $port, $matches)) {
                    $port = $matches[1];
                    $protocol = 'udp';
                }
                if (in_array('PUBLIC', $categories)) {
                    $rules[] = '# allow all from categories ' . implode(', ', $match_rule_categories[$port]);
                    $rules[] = 'iptables -A INPUT -p ' . $protocol . ' --dport ' . $port . ' -j ACCEPT';
                } else {
                    $rules[] = '# allow ' . implode(', ', $categories) . ' from categories ' . implode(', ', $match_rule_categories[$port]);
                    foreach ($this->getIPsFromCategories($categories) as $src_ip) {
                        if ($ip == $src_ip) {
                            continue;
                        }
                        $rules[] = 'iptables -A INPUT -p ' . $protocol . ' -s ' . $src_ip . ' --dport ' . $port . ' -j ACCEPT';
                    }
                }
            }
            file_put_contents(__DIR__ . '/outputs/' . $ip . '.sh', implode("\n", $rules) . "\n");
            file_put_contents(__DIR__ . '/outputs/' . $ip . '_test.sh', implode("\n", array_merge($rules, $this->testSuffix())) . "\n");
            chmod(__DIR__ . '/outputs/' . $ip . '.sh', 0755);
            chmod(__DIR__ . '/outputs/' . $ip . '_test.sh', 0755);
        }
    }
}

$g = new FirewallGenerator;
$g->main();
