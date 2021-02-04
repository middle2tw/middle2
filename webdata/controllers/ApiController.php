<?php

class ApiController extends Pix_Controller
{
    public function init()
    {
    }

    public function checkelasticAction()
    {
        $index = strval($_GET['index']);
        $secret = $_GET['secret'];
        $sig = crc32($index . $secret . getenv('ELASTIC_SECRET'));
        if ($_GET['sig'] != $sig) {
            return $this->json(array('error' => true, 'message' => 'wrong sig'));
        }
        if (!$addon = Addon_Elastic::search(array('index' => $index))->first()) {
            return $this->json(array('error' => true, 'message' => 'wrong index'));
        }

        if ($addon->secret != $secret) {
            return $this->json(array('error' => true, 'message' => 'wrong secret'));
        }

        return $this->json(array('error' => false));
    }

    public function weblogAction()
    {
        // TODO: 要限內網並且加上 secret key
        if (!$logs = json_decode($_POST['data'])) {
            throw new Exception('invalid data');
        }

        $messages = array();
        foreach ($logs as $log) {
            $messages[] = array(
                'category' => "app-{$log->project}-error",
                'message' => $log->time . ' ' . $log->id . ' ' . urlencode($log->log),
            );
        }
        Logger::log($messages);
        return $this->json(1);
    }

    public function updatemachinestatusAction()
    {
        // TODO: 要判斷只有內網可以
        $name = strval($_GET['name']);
        $status = $_POST['status'];

        $machine = Machine::find_by_ip(ip2long($_SERVER['REMOTE_ADDR']));
        if (!$machine) {
            return $this->json(1);
        }

        MachineStatus::insert(array(
            'machine_id' => $machine->machine_id,
            'status' => $status,
            'updated_at' => time(),
        ));

        return $this->json(1);
    }
}
