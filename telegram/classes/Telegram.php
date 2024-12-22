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
        /*
        $request = curl_init("https://api.telegram.org/bot".self::$token."/".$method);
        curl_setopt($request,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request,CURLOPT_POST,1);
        curl_setopt($request,CURLOPT_POSTFIELDS, $data);
        curl_setopt($request,CURLOPT_TIMEOUT,10);
        $result = curl_exec($request);
        if (curl_errno($request)) {
            die(curl_error($request));
        }
        curl_close($request);
        $data = json_decode($result,1);
        if($data['ok'] != true) {
            die("error in request");
        }
        return $data;
        */
    }
    public static function updates () : array
    {
        return json_decode(file_get_contents("php://input"),1) ?? [];
    }
}