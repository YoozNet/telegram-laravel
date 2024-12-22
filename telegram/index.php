<?php

header('Content-Type: application/json; charset=utf-8');
ini_set('error_log', '0.txt');
error_reporting(E_ALL);

include_once("boot.php");

$Updates = new TelegramUpdates();
file_put_contents("test_updates.json",json_encode($Updates->update,128|256));
if($Updates->text == "/start") {
    // createUser('193191319313')
    Telegram::api('sendMessage',[
        'chat_id'=>$Updates->chat_id,
        'text'=>'خوش آمدید : ef: '.$Updates->referral
    ]);
}