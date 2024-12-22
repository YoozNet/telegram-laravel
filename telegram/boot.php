<?php

include_once("vendor/autoload.php");
use Dotenv\Dotenv;
$env = Dotenv::createImmutable("../../");
$env->load();


Telegram::setToken($_ENV['TELEGRAM_BOT_TOKEN']);