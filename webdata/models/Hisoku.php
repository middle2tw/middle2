<?php

class Hisoku
{
    protected static function _getIPsByGroup($group)
    {
        $ips = array();
        foreach (Machine::getMachinesByGroup($group) as $machine) {
            $ips[] = long2ip($machine->ip);
        }
        return $ips;
    }

    public function getLoadBalancers()
    {
        return self::_getIPsByGroup('loadbalancer');
    }

    public function getMySQLServers()
    {
        return self::_getIPsByGroup('mysql');
    }

    public function getNodeServers()
    {
        return self::_getIPsByGroup('nodes');
    }

    public function getPgSQLServers()
    {
        return self::_getIPsByGroup('pgsql');
    }

    public function getSearchServers()
    {
        return self::_getIPsByGroup('search');
    }

    public static function getLoginUser()
    {
        if ($u = User::find(intval(Pix_Session::get('user')))) {
            return $u;
        }
        return false;
    }

    public static function getStoken()
    {
        if (!$sToken = Pix_Session::get('sToken')) {
            $sToken = crc32(uniqid());
            Pix_Session::set('sToken', $sToken);
        }

        return $sToken;
    }

    public static function uniqid($length)
    {
        $set = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $ret = '';
        for ($i = 0; $i < $length; $i ++) {
            $ret .= $set[rand(0, strlen($set) - 1)];
        }
        return $ret;
    }

    public static function alert($title, $body)
    {
        if (!class_exists('AmazonSNS')) {
            define('AWS_DISABLE_CONFIG_AUTO_DISCOVERY', true);
            include(__DIR__ . '/../stdlibs/sdk-1.6.0/sdk.class.php');
            if (!getenv('HEALTHCHECK_KEY') or !getenv('HEALTHCHECK_SECRET')) {
                throw new Exception('env HEALTHCHECK_KEY & HEALTHCHECK_SECRET not found');
            }
            CFCredentials::set(array(
                'development' => array(
                    'key' => getenv('HEALTHCHECK_KEY'),
                    'secret' => getenv('HEALTHCHECK_SECRET'),
                ),
            ));
        }
        $sns = new AmazonSNS();
        $sns->set_region(AmazonSNS::REGION_TOKYO);
        $sns->publish('arn:aws:sns:ap-northeast-1:391093844476:Hisoku-Health', $body, array('Subject' => $title));
    }
}
