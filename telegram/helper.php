<?php

if(!function_exists('systemLog')) {
    function systemLog($message,$code)
    {
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
        return 'YN-'.$uuid;
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
        return Database::select("YN_users", ["*"], "user_id = ?", [$userId])[0];
    }
}
if(!function_exists("userExists")) {
    function userExists($userId): bool
    {
        return true;
    }
}
if(!function_exists("adminCardNumber")) {
    function adminCardNumber($userId) {
        $userData = getUser($userId);
        $adminCard = $userData['admin_bank_card_id'];
        if(is_null($adminCard)) {
            return null;
        }
        $findCard = Database::select("YN_admin_bank_cards", ["id","bank","card_number"], "id = ?", [$adminCard])[0];
        return ['id'=>$findCard['id'],'bank'=>$findCard['bank'],'card_number'=>$findCard['card_number']];
    }
}
if(!function_exists("createUser")) {
    function createUser($userId,$try=0): bool
    {
        $unique_columns = ['yn_users_api_token_unique','yn_users_referral_id_unique'];
        if ($try >= 10) {
            systemLog('could not generate unique id for ('.implode("|",$unique_columns).')',-100);
            die();
        }
        try {
            return Database::create('YN_users',
            ['user_id','referral_id','api_token'],
                [$userId,generateString(),generateUUID()]
            );
        } catch (\PDOException $error) {
            $message = $error->getMessage();
            $regex = "Duplicate entry '(.*)' for key '(".implode("|",$unique_columns).")'";
            preg_match("#".$regex."#",$message,$data);
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

if(!function_exists("getAdminCards")) {
    function getAdminCards($just_active=true): array
    {
        $where = ($just_active == true) ? "status = 0" : null;
        return Database::select("YN_admin_bank_cards", ["bank","card_number","id"], $where);
    }
}

if(!function_exists("splitCardNumber")) {
    function splitCardNumber ($card_number) {
        $card_number = trim($card_number);
        return rtrim(preg_replace('/(.{4})/','$1-',$card_number),"-");
    }
    echo splitCardNumber ('1234567812341234');
}

if(!function_exists('setUserStep')) {
    function setUserStep($userId, $step) {
        $getData = getUser($userId);
        $getData = json_decode($getData['data'],1);
        $getData['step'] = $step;
        return Database::update('YN_users', ['data'],[json_encode($getData)], 'user_id =?', [$userId]);
    }
}

if(!function_exists('setUserStep')) {
    function getUserStep($userId) {
        $getData = getUser($userId);
        $getData = json_decode($getData['data'],1);
        return $getData['step'];
    }
}


if(!function_exists('setBack')) {
    function setBackTo($userId, $back_to,$as="text",$delete_message=true) {
        $getData = getUser($userId);
        $getData = json_decode($getData['data'],1);
        $getData['back'] = ['to'=>$back_to, 'as'=>$as, 'delete_message'=>$delete_message];
        return Database::update('YN_users', ['data'],[json_encode($getData)], 'user_id =?', [$userId]);
    }
}


if(!function_exists('getBack')) {
    function getBack($userId) {
        $getData = getUser($userId);
        $getData = json_decode($getData['data'],1);
        return $getData['back'];
    }
}

if(!function_exists('setUserIP')) {
    function setUserIP($userId,$ip_address) {
        return Database::update('YN_users', ['ip_address'],[$ip_address], 'user_id =?', [$userId]);
    }
}
