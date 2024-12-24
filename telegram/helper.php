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
        $findCard = Database::select("YN_admin_bank_cards", ["id","bank","card_number","card_image_file_id"], "id = ?", [$adminCard])[0];
        # return ['id'=>$findCard['id'],'bank'=>$findCard['bank'],'card_number'=>$findCard['card_number'],"card_image_file_id"=>];
        return $findCard;
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
            ['user_id','referral_id','api_token','data', 'created_at', 'updated_at'],
                [$userId,generateString(),generateUUID(),json_encode([
                    "back" => [
                        "to" => "none",
                        "as" => "none",
                        "delete_message" => false
                    ],
                    "step" => "none"
                ]),date("Y-m-d H:i:s"), date("Y-m-d H:i:s")]
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
        return Database::select("YN_admin_bank_cards", ["*"], $where);
    }
}

if(!function_exists("getAdminCardById")) {
    function getAdminCardById($id): array
    {
        return Database::select("YN_admin_bank_cards", ["*"], 'id = ?',[$id])[0];
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

if(!function_exists('getUserStep')) {
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

if(!function_exists('GetConfig')) {
    function GetConfig() {
        $value = require '../../config/site-settings.php';
        return $value;
    }
}

if (!function_exists('displayNumber')) {
    function displayNumber($number, $withDecimals = false){
        if (strpos((string)$number, '.') !== false) {
            if ($withDecimals) {
                $parts = explode('.', (string)$number);
                $decimalPart = substr($parts[1], 0, 4);
                return $parts[0] . '.' . $decimalPart;
            } else {
                return (int)$number;
            }
        } else {
            return $number;
        }
    }
}

if (!function_exists('getBankName')) {
    function getBankName(string $key){
        $banks = include 'classes/banks.php';
        if (isset($banks['lists'][$key]) && isset($banks['lists'][$key]['name'])) {
            return $banks['lists'][$key]['name'];
        }
        return $banks['lists']['UNKNOWN']['name'];
    }
    
}

if (!function_exists('formatWallet')) {
    function formatWallet(float $amount): string
    {
        if (fmod($amount, 1) == 0) {
            return displayNumber((int)$amount);
        } else {
            return displayNumber($amount, true);
        }
    }
}

if (!function_exists('LoginToken')) {
    function LoginToken($userId) {
        $getData = getUser($userId);
        $tokenData = Database::select("YN_login_tokens", ["*"], "user_id = ? ORDER BY id DESC LIMIT 1", [$getData['id']])[0];
        if ($tokenData) {
            $consumedAt = $tokenData['consumed_at'];
            $expiresAt = $tokenData['expires_at'];
            if (is_null($consumedAt) && strtotime($expiresAt) > time()) {
                return generateLoginLink($tokenData['token'], strtotime($expiresAt));
            } 
        } 
        $newToken = generateString(24);
        $expiresAt = date("Y-m-d H:i:s", strtotime("+15 minutes"));

        Database::create('YN_login_tokens', 
            ['user_id', 'token', 'expires_at', 'consumed_at','created_at','updated_at'], 
            [$getData['id'], $newToken, $expiresAt, null,date("Y-m-d H:i:s"),date("Y-m-d H:i:s")]
        );
        return generateLoginLink($newToken,strtotime($expiresAt));
    }
}

if (!function_exists('generateLoginLink')) {
    function generateLoginLink($token, $expiresAt) {
        global $_ENV;
        $baseUrl = GetConfig()['url_unfiltered'];

        $params = [
            'expires' => $expiresAt,
        ];

        $query = http_build_query($params);
        $link = 'https://'. $baseUrl . '/login/' . $token . '?' . $query;
        $secretKey = base64_decode(substr($_ENV['APP_KEY'], 7));
        $signature = hash_hmac('sha256', $link, $secretKey);
    
        $finalLink = $link . '&signature=' . $signature;
    
        return $finalLink;
    }    
}

if(!function_exists('getCardsBank'))
{
    function getCardsBank($userIdTable,$just_active=true): array {
        $where = ($just_active == true) ? "status = 1 AND " : null;
        $where .= "user_id = ".$userIdTable;
        return Database::select("YN_bank_cards", ["id","bank","card_number"], $where);
    }
}

if(!function_exists('getCardById')) {
    function getCardById($cardID) {
        return Database::select("YN_bank_cards", ["*"], "id =?", [$cardID])[0];
    }
}

if(!function_exists('getBankByName'))
{
    function getBankByName($bankName,$just_active=true) {
        $where = ($just_active == true) ? "status = 0 AND " : null;
        $where .= "bank = ?";
        return Database::select("YN_admin_bank_cards", ["*"], $where, [$bankName]);
    }
}

if(!function_exists('setUserTmp')) {
    function setUserTmp($userId,$key,$value) {
        $getData = getUser($userId);
        $getData = json_decode($getData['data'],1);
        $getData['tmp'][$key] = $value;
        return Database::update('YN_users', ['data'],[json_encode($getData)], 'user_id =?', [$userId]);
    }
}

if(!function_exists('getUserTmp')) {
    function getUserTmp($userId,$key) {
        $getData = getUser($userId);
        $getData = json_decode($getData['data'],1);
        return $getData['tmp'][$key];
    }
}


if(!function_exists('getAllUserTmp')) {
    function getAllUserTmp($userId) {
        $getData = getUser($userId);
        $getData = json_decode($getData['data'],1);
        return $getData['tmp'];
    }
}

if (!function_exists('GenerateTaxPrice')) {
    function GenerateTaxPrice($price) {
        if ($price < 10000) {
            return 100;
        }
        if ($price < 1700000) {
            $increments = intdiv($price - 10000, 50000);
            $minRand = 100 + ($increments * 50);
            $maxRand = $minRand + 50;
            return rand($minRand, $maxRand);
        }
        return 2000;
    }
}

if (!function_exists('getFactors')) {
    function getUserInvoices($user_id,$limit=10)
    {
        $where = "user_id = ?";
        return Database::select("YN_invoices", ["*"], $where, [$user_id],$limit);
    }

}