<?php

class API
{
    private static $token = "YOUR_SHARED_SECRET";
    private static $apiSecret = "TeleGram-API-Token-@@";
    public static function request($url, $data = [])
    {
        $client = new GuzzleHttp\Client([
            'base_uri' => 'https://yooznet.online/api/',
            'timeout'  => 30.0
        ]);
        $body = json_encode($data);
        $signature = hash_hmac('sha256', $body, self::$apiSecret);
        $timestamp = time();
        $response = $client->request('POST', $url, [
            'json' => $data, 
            'headers' => [
                'Authorization' => 'Bearer ' . self::$token,  
                'HMAC-Signature' => $signature,  
                'Timestamp' => time(), 
                'Content-Type' => 'application/json',  
            ]
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }
    public static function sendInvoice($invoiceData)
    {
        return self::request('tg/invoice', $invoiceData); 
    }
    public static function sendCard($data)
    {
        return self::request('tg/card', $data); 
    }
    public static function sendTicket($data)
    {
        return self::request('tg/ticket', $data); 
    }
    public static function buyservice($data)
    {
        return self::request('tg/service/buy', $data); 
    }
    public static function addtraffic($data)
    {
        return self::request('tg/service/add-traffic', $data); 
    }
    public static function renew($data)
    {
        return self::request('tg/service/renew', $data); 
    }
}