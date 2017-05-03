<?php

class NotifyLib
{
    public function alert($title, $body, $to)
    {
        if (!class_exists('AmazonSES')) {
            define('AWS_DISABLE_CONFIG_AUTO_DISCOVERY', true);
            include(__DIR__ . '/../stdlibs/sdk-1.6.0/sdk.class.php');
            if (!getenv('SES_KEY') or !getenv('SES_SECRET')) {
                throw new Exception('env SES_KEY & SES_SECRET not found');
            }
            CFCredentials::set(array(
                'development' => array(
                    'key' => getenv('SES_KEY'),
                    'secret' => getenv('SES_SECRET'),
                ),
            ));
        }
        $ses = new AmazonSES();
        //$ses->set_region(AmazonSES::REGION_TOKYO);
        $ret = $ses->send_email(
            getenv('SES_MAIL'),
            array(
                'ToAddresses' => array($to),
            ),
            array(
                'Subject.Data' => $title,
                'Body.Text.Data' => $body,
            )
        );
        return $ret;
    }
}
