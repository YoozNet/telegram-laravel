<?php

class Telegram {
    private static $token;
    public static function setToken($token): void
    {
        self::$token = $token;
    }
    public static function api($method,$data=[]) : \Psr\Http\Message\ResponseInterface 
    {
        $client = new GuzzleHttp\Client([
            'base_uri' => 'https://api.telegram.org/bot' . self::$token . '/',
            'timeout'  => 2.0
        ]);
        if(isset($data['reply_markup'])) {
            $data['reply_markup'] = json_encode($data['reply_markup']);
        }
        
        $response = $client->request('POST', $method, ['query' => $data]);
        return $response->getBody();
    }
    public static function file($file_id) {
        $response = self::api('getFile', ['file_id' => $file_id]);
        $body = json_decode($response->getBody(), true);

        if (isset($body['ok']) && $body['ok'] && isset($body['result']['file_path'])) {
            return $body['result']['file_path'];
        }

        return null;
    }
}