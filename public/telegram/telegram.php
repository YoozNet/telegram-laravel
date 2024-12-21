<?php

class Telegram {
    public static function Api($method,$data=[]) : array
    {
        $BOT_TOKEN = '';
        $Request = curl_init("https://api.telegram.org/bot".$BOT_TOKEN."/".$method);
        curl_setopt($Request,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($Request,CURLOPT_POST,1);
        curl_setopt($Request,CURLOPT_POSTFIELDS, $data);
        curl_setopt($Request,CURLOPT_TIMEOUT,10);
        $result = curl_exec($Request);
        if (curl_errno($Request)) {
            die(curl_error($Request));
        }
        curl_close($Request);
        $data = json_decode($result,1);
        if($data['ok'] != true) {
            die("error in request");
        }
        return $data;
    }
    public static function Updates () : array
    {
        return json_decode(file_get_contents("php://input"),1);
    }
}