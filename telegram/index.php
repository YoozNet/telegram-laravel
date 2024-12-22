<?php

header('Content-Type: application/json; charset=utf-8');
ini_set('error_log', '0.txt');
error_reporting(E_ALL);

include_once("boot.php");

$Updates = new TelegramUpdates();
file_put_contents("test_updates.json",json_encode($Updates->update,128|256));

if(isset($Updates->text) and $Updates->text == "/start" or explode(" ",$Updates->text)[0] == "/start") {
    $referral = explode(" ",$Updates->text);
    if(isset($referral[1])) {
        Telegram::api('sendMessage',[
            'chat_id'=>$Updates->chat_id,
            'text'=>'ورود با دعوت'.$referral
        ]);
    }
    // createUser('193191319313')
    Telegram::api('sendMessage',[
        'chat_id'=>$Updates->chat_id,
        'text'=>'خوش آمدید : ef: '.$Updates->referral
    ]);
}