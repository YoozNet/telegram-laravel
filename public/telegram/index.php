<?php

include_once("boot.php");

$Updates = Telegram::Updates();

Telegram::Api('sendMessage',[
    'chat_id'=>000000,
    'text'=>'hello world'
]);