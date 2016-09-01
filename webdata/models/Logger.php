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

    /**
     * 只塞一筆記錄進 scribe
     * 
     * @param array $message array('category' => 'xxx', 'message' => 'xxx')
     * @static
     * @access public
     * @return void
     */
    public static function logOne($message)
    {
        return self::log(array($message));
    }

    /**
     * 取得這個 category 的 log，預設一次抓 20 行
     * 
     * @param string $category 
     * @param array $options cursor-after
     *                       cursor-before
     *                       line (default: 20)
     * @static
     * @access public
     * @return array(logs, cursor[start-pos, end-pos])
     */
    public static function getLog($category, $options = array())
    {
        // 抓最新的 $perbyte bytes ，想辦法湊到 20 行
        $perbyte = 1024;
        $line = array_key_exists('line', $options) ? intval($options['ine']) : 20;

        $log_files = glob("/srv/logs/scribed/{$category}/{$category}-*");

        $return_logs = array();
        $return_cursor = array();

        if (array_key_exists('cursor-after', $options)) {
            foreach ($log_files as $log_file) {
                $filename = substr(basename($log_file), strlen($category) + 1);
                $cursor = 0;

                if (array_key_exists('cursor-after', $options)) {
                    if ($options['cursor-after']['file'] != $filename) {
                        continue;
                    }
                    $cursor = $options['cursor-after']['cursor'];
                    unset($options['cursor-after']);
                }

                //error_log("opening {$log_file}...");
                $filesize = filesize($log_file);
                if (0 == $filesize) {
                    continue;
                }
                $fp = fopen($log_file, 'r');
                if ($cursor) {
                    fseek($fp, $cursor);
                }

                // 每次爬一行
                while ($log = fgets($fp)) {
                    // 從 $cursor 往前爬 $perbyte byte
                    $log = trim($log);
                    if ('' == $log) {
                        $cursor ++;
                        continue;
                    }

                    // 第一筆 log ，需要記錄 cursor-start
                    if (count($return_logs) == 0) {
                        $return_cursor['cursor-start'] = array($filename, $cursor);
                    }
                    array_push($return_logs, $log);
                    $cursor += (strlen($log) + 1);
                    if (count($return_logs) >= $line) {
                        break 2;
                    }
                }
            }
            $return_cursor['cursor-end'] = array($filename, $cursor);
            return array($return_logs, $return_cursor);
        }

        rsort($log_files);

        // 照時間排序的 log 檔
        foreach ($log_files as $log_file) {
            $filename = substr(basename($log_file), strlen($category) + 1);
            $cursor = null;

            if (array_key_exists('cursor-before', $options)) {
                if ($options['cursor-before']['file'] != $filename) {
                    continue;
                }
                $cursor = $options['cursor-before']['cursor'];
                unset($options['cursor-before']);
            }
            if (0 == filesize($log_file)) {
                continue;
            }
            if (strpos($log_file, '.gz')) {
                $fp = gzopen($log_file, 'r');
            } else {
                $fp = fopen($log_file, 'r');
            }

            if (is_null($cursor)) {
                $cursor = filesize($log_file);
            }

            $current_perbyte = $perbyte;
            // 每次爬 $perbyte byte
            while ($cursor > 0) {
                // 從 $cursor 往前爬 $current_perbyte byte
                if ($cursor > $current_perbyte) {
                    //error_log("loading {$log_file} before {$cursor} - {$current_perbyte}...");
                    fseek($fp, $cursor - $current_perbyte);
                    $content = fread($fp, $current_perbyte);
                    $head = false;
                } else {
                    fseek($fp, 0);
                    //error_log("loading {$log_file} from start...");
                    $content = fread($fp, $cursor);
                    $head = true;
                }

                $logs = explode("\n", $content);
                // 換成新到舊
                $logs = array_reverse($logs);

                if (!$head) {
                    // 如果沒有到頭的話，第一行拿掉，因為可能是被腰斬
                    array_pop($logs);
                    // pop 完後沒任何東西，就表示只有一行被腰斬還超過 $current_perbyte, 所以應該加倍再來一次
                    if (!$logs) {
                        $current_perbyte *= 2;
                        continue;
                    }
                }
                $current_perbyte = $perbyte;

                foreach ($logs as $log) {
                    if ('' == $log) {
                        $cursor --;
                        continue;
                    }

                    // 第一筆 log ，需要記錄 cursor-start
                    if (count($return_logs) == 0) {
                        $return_cursor['cursor-end'] = array($filename, $cursor);
                    }
                    array_unshift($return_logs, $log);
                    $cursor -= (strlen($log) + 1);
                    if (count($return_logs) >= $line) {
                        break 3;
                    }
                }
            }
        }

        $return_cursor['cursor-start'] = array($filename, $cursor);
        return array($return_logs, $return_cursor);
    }
}
