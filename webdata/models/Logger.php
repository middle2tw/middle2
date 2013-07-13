<?php

define('THRIFT_ROOT', __DIR__ . '/../stdlibs/Thrift');
require_once THRIFT_ROOT.'/packages/FacebookService.php';
require_once THRIFT_ROOT.'/packages/scribe.php';
require_once THRIFT_ROOT.'/packages/Types.php';

class Logger
{
    protected static $_scribe_client = null;

    public static function getScribeClient()
    {
        if (is_null(self::$_scribe_client)) {
            $socket = new Thrift\Transport\TSocket(getenv('SCRIBE_HOST'), getenv('SCRIBE_PORT'), true);
            $transport = new Thrift\Transport\TFramedTransport($socket);
            $protocol = new Thrift\Protocol\TBinaryProtocol($transport, false, false);
            $scribeClient = new Scribe\Thrift\scribeClient($protocol, $protocol);
            $transport->open();
            self::$_scribe_client = $scribeClient;
        }
        return self::$_scribe_client;
    }

    /**
     * 記錄進 scirbe
     * 
     * @param array $messages array(array('category' => xxx, 'message' => xxx), ...)
     * @static
     * @access public
     * @return void
     */
    public static function log($messages)
    {
        $scribeClient = self::getScribeClient();
        $log_entries = array();
        foreach ($messages as $message) {
            $log_entry = new Scribe\Thrift\LogEntry($message);
            $log_entries[] = $log_entry;
        }
        $scribeClient->Log($log_entries);
    }
}
