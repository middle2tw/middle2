<?php

class AWS
{
    public static function getHostIP()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'http://169.254.169.254/latest/meta-data/local-hostname');
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if (!$hostname = curl_exec($curl)) {
            trigger_error(curl_error($curl));
            return false;
        }
        curl_close($curl);

        // match ip-10-0-0-xx
        if (preg_match('#^ip-([0-9-]*)$#', $hostname, $matches)) {
            return ip2long(str_replace('-', '.', $matches[1]));
        }

        if (!$ip = gethostbyname($hostname)) {
            return false;
        }

        return ip2long($ip);
    }
}
