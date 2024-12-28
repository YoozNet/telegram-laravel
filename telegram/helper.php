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
        $findCard = Database::select("YN_admin_bank_cards", ["*"], "id = ?", [$adminCard])[0];
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
        $getData = json_decode($getData['data'] ?? '[]',1);
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
    function setBackTo($userId, $back_to,$as="text",$delete_message=true,$new_message=false) {
        $getData = getUser($userId);
        $getData = json_decode($getData['data'],1);
        $getData['back'] = ['to'=>$back_to, 'as'=>$as, 'delete_message'=>$delete_message,'new_message'=>$new_message];
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
if(!function_exists('GetDepartments')) {
    function GetDepartments(string $key) {
        $value = require '../../config/ticket.php';
        if (isset($value['departments']) && isset($value['departments'][$key])) {
            return $value['departments'][$key];
        }
        return "ناشناخته";
    }
}
if(!function_exists('GetAllDepartments')) {
    function GetAllDepartments() {
        return require '../../config/ticket.php';
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
        if(isset($getData['tmp'])) {
            return $getData['tmp'][$key] ?? null;
        }
        return null;
    }
}

if(!function_exists('deleteUserTmp')) {
    function deleteUserTmp($userId,array $keys) {
        $allTmps = getAllUserTmp($userId);
        foreach ($keys as $key) {
            unset($allTmps[$key]);
        }
        return Database::update('YN_users', ['data'],[json_encode($allTmps)], 'user_id =?', [$userId]);
    }   
}

if(!function_exists('clearUserTmp')) {
    function clearUserTmp($userId) {
        return Database::update('YN_users', ['data'],[json_encode([])], 'user_id =?', [$userId]);
    }   
}

if(!function_exists('getAllUserTmp')) {
    function getAllUserTmp($userId) {
        $getData = getUser($userId);
        $getData = json_decode($getData['data'],1);
        return $getData['tmp'] ?? [];
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

if (!function_exists('getUserInvoices')) {
    function getUserInvoices($user_id,$limit=10)
    {
        $where = "user_id = ? AND currency = 'IRT'";
        return Database::select("YN_invoices", ["*"], $where, [$user_id],$limit,null,'id');
    }
}
if(!function_exists('getInvoice')) {
    function getInvoice($invoiceID) {
        return Database::select("YN_invoices", ["*"], "id =?", [$invoiceID])[0];
    }
}

if (!function_exists('getUserBankCards')) {
    function getUserBankCards($user_id,$limit=10)
    {
        $where = "user_id = ?";
        $orderBy = "status = 1 DESC, id";
        return Database::select("YN_bank_cards", ["*"], $where, [$user_id],$limit,null,$orderBy);
    }
}

if(!function_exists('getbankcard')) {
    function getbankcard($id) {
        return Database::select("YN_bank_cards", ["*"], "id =?", [$id])[0];
    }
}
if (!function_exists('getUserBankCardsActive')) {
    function getUserBankCardsActive($id)
    {
        return Database::select("YN_bank_cards", ["*"], "user_id =? AND status = '1'", [$id]);
    }
}
if (!function_exists('getUserBankCardsPending')) {
    function getUserBankCardsPending($id)
    {
        return Database::select("YN_bank_cards", ["*"], "user_id =? AND (status = '0' OR status = '1')", [$id]);
    }
}

if(!function_exists('addUserBankCard')) {
    function addUserBankCard($userId,$card_number,$card_image_file_id,$status=0) {
        return Database::create('YN_bank_cards', 
        ['user_id', 'card_number','card_image_file_id', 'created_at', 'updated_at','status'], 
        [$userId, $card_number, $card_image_file_id,date("Y-m-d H:i:s"), date("Y-m-d H:i:s"),$status]
        );
    }
}

if(!function_exists('getUserCardBankByNumber')) {
    function getUserCardBankByNumber($cardNumber) {
        return Database::select("YN_bank_cards", ["*"], "card_number =?", [$cardNumber])[0];
    }
}

if(!function_exists('checkUserCardBankExists')) {
    function checkUserCardBankExists($cardNumber) {
        return Database::select("YN_bank_cards", ["*"], "card_number = ? AND (status = 0 OR status = 1)", [$cardNumber])[0];
    }
}

if (!function_exists('getUserTickets')) {
    function getUserTickets($user_id,$limit=10)
    {
        $where = "user_id = ?";
        $orderBy = "CASE WHEN status = 3 THEN 0 WHEN status = 1 THEN 1 WHEN status = 2 THEN 2 ELSE 3 END, id";
        return Database::select("YN_tickets", ["*"], $where, [$user_id],$limit,null,$orderBy);
    }
}

if(!function_exists('getTicketMessage')) {
    function getTicketMessage ($ticketId) 
    {
        return Database::select("YN_ticket_messages", ["*"], 'ticket_id = ?', [$ticketId],null,null,'id');
    }
}

if(!function_exists('getTicketData')) {
    function getTicketData ($ticketId) 
    {
        return Database::select("YN_tickets", ["*"], 'id = ?', [$ticketId])[0];
    }
}

if(!function_exists('GetAllServices')) {
    function GetAllServices() {
        $config = GetConfig();
        return $config['services'];
    }
}

if(!function_exists('getServicePrice')) {
    function getServicePrice($userId,$serviceType) {
        $userData = getUser($userId);
        $config = GetConfig();
        $PriceWithoutProfit = $config['PriceWithoutProfit'][$serviceType];
        $PricePerGB = $config['services'][$serviceType]['price_per_gig'];
        $price = $PricePerGB - $PriceWithoutProfit;
        $discount = $userData['discount'];
        $price = $price - ($price * $discount / 100);
        $price = $price + $PriceWithoutProfit;
        return [
            'yc' => displayNumber($price,true),
            'irt' => displayNumber($config['yc_price'] * $price)
        ];
    }
}

if(!function_exists('getUserService')) {
    function getUserService ($userId) {
        return Database::select("YN_services", ["*"], 'user_id = ?', [$userId],null,null,'id');
    }
}

if(!function_exists('serverIdToType')) {
    function serverToType($server_id) {
        $server = Database::select("YN_servers", ["location"], 'id = ?', [$server_id])[0];
        $locations = [
            "IR" => 'tunnel',
            "DE" => 'direct',
            "TR" => 'gaming',
            "NL" => 'secure',
            "FI" => 'unlimited'
        ];
        return $locations[$server['location']];
    }
}