<?php

include_once("vendor/autoload.php");
use Dotenv\Dotenv;
$env = Dotenv::createImmutable("../../");
$env->load();

// Telegram::setToken($_ENV['TELEGRAM_BOT_TOKEN']);
Telegram::setToken("1060413360:AAGj_JM6bztQ7JipzwNfD2pRy6cFv_eP2tM");