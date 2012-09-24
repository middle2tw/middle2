<?php

class AWS
{
    public function getHostIP()
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

        if (!$ip = gethostbyname($hostname)) {
            return false;
        }

        return ip2long($ip);
    }
}
