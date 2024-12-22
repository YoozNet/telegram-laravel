<?php

class Telegram {
    private static $token;
    public static function setToken($token): void
    {
        self::$token = $token;
    }
    public static function api($method,$data=[]) : \Psr\Http\Message\ResponseInterface 
    {
        $client = new GuzzleHttp\Client();
        if(isset($data['reply_markup'])) {
            $data['reply_markup'] = json_encode($data['reply_markup']);
        }
        $response = $client->request("post","https://api.telegram.org/bot".self::$token."/".$method,['query'=>$data]);
        return $response;
    }
}