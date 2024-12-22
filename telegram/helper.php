<?php

if(!function_exists('systemLog')) {
    function systemLog($message,$code)
    {
        var_dump("error ID: $code - error Message: $message");
        error_log("error ID: $code - error Message: $message");
    }
}

if(!function_exists("generateUUID")) {
    function generateUUID() {
        $uuid = sprintf(
            '%s-%s-%s-%s-%s',
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(2)),
            bin2hex(random_bytes(2)),
            bin2hex(random_bytes(2)),
            bin2hex(random_bytes(6))
        );
        return $uuid;
    }
}

if(!function_exists("generateString")) {
    function generateString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($characters, ceil($length / strlen($characters)))), 0, $length);
    }
}

if(!function_exists("getUser")) {
    function getUser($userId): array
    {
        return [];
    }
}
if(!function_exists("userExists")) {
    function userExists($userId): bool
    {
        return true;
    }
}

if(!function_exists("createUser")) {
    function createUser($userId,$try=0): bool
    {
        if ($try >= 10) {
            systemLog('could not generate unique uuid',-100);
            die();
        }
        try {
            return Database::create('YN_users',
            ['user_id','referral_id','api_token'],
                [$userId,generateString(),generateUUID()]
            );
        } catch (\PDOException $error) {
            $message = $error->getMessage();
            preg_match("#Duplicate entry '(.*)' for key 'api_token'#",$message,$data);
            if(isset($data[1]))
            {
                createUser($userId,$try+1);
            } else {
                systemLog($message,-400);
                return false;
            }
        }
    }
}