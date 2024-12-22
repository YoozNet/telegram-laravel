<?php

header('Content-Type: application/json; charset=utf-8');
ini_set('error_log', '0.txt');
error_reporting(E_ALL);

include_once("boot.php");

$Updates = Telegram::updates();
var_dump(createUser('1234567'));
/*
Telegram::api('sendMessage',[
    'chat_id'=>000000,
    'text'=>'hello world'
]);
*/