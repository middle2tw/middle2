<?php

class Elastic
{
	protected static $_url;
    protected static $_user;
    protected static $_password;

    public static function login($url, $user, $password)
    {
		self::$_url = $url;
        self::$_user = $user;
        self::$_password = $password;
    }

    public static function esQuery($url, $method = 'GET', $data = null)
    {
        $curl = curl_init(self::$_url . $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if ($method != 'GET') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($curl, CURLOPT_USERPWD, self::$_user . ':' . self::$_password);
        if (!is_null($data)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        $content = curl_exec($curl);
        if ($content === false) {
            throw new Exception('curl_exec error: ' . curl_error($curl));
        }

        $info = curl_getinfo($curl);
        curl_close($curl);
        $ret = json_decode($content);
        if (is_object($ret) and property_exists($ret, 'error')) {
            print_r($ret->error);
            throw new Exception(json_encode($ret->error->root_cause), $ret->status);
        }
        return $ret;
    }

    public static function getUsers()
    {
        $users = [];
        $roles = [];
        $ret = new StdClass;
        foreach (self::esQuery('/_security/user/') as $data) {
            if (!preg_match('#.*-.*-.*#', $data->username)) {
                continue;
            }
            $users[$data->username] = new StdClass;
            $users[$data->username]->user_name = $data->username;
            foreach ($data->roles as $role) {
                $roles[$role] = $role;
            }
        } 
        foreach (self::esQuery('/_security/role/') as $role_name => $data) {
            if (!array_key_exists($role_name, $roles)) {
                continue;
            }
            $users[$role_name]->prefix = $data->indices[0]->names[0];
        } 
        return $users;
    }

    public static function createUser($user, $password, $prefix)
    {
        self::esQuery('/_security/role/' . $user, 'PUT', json_encode([
            'cluster' => ['all'],
            'indices' => [
                [
                    'names' => [ $prefix . '*' ],
                    "privileges" => ["all"],
                ],
            ],
        ]));
        return self::esQuery('/_security/user/' . $user, 'PUT', json_encode([
            'username' => $user,
            'password' => $password,
            'roles' => [$user],
        ]));
    }

    public static function dropUser($user)
    {
        self::esQuery('/_security/user/' . $user, 'DELETE', '');
        self::esQuery('/_security/role/' . $user, 'DELETE', '');
    }
}
