<?php

include_once("boot.php");

$Updates = Telegram::updates();

Telegram::api('sendMessage',[
    'chat_id'=>000000,
    'text'=>'hello world'
]);