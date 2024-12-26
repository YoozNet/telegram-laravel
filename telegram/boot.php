<?php

include_once("vendor/autoload.php");
include_once("../../app/Enum/UserGroupEnum.php");
include_once("../../app/Enum/InvoiceStatus.php");
include_once("../../app/Enum/BankCardStatus.php");
include_once("../../app/Enum/TicketStatus.php");
include_once("classes/banks.php");
use Dotenv\Dotenv;
$env = Dotenv::createImmutable("../../");
$env->load();
/*
Telegram::setToken($_ENV['TELEGRAM_BOT_TOKEN']);
*/

Telegram::setToken("1060413360:AAGj_JM6bztQ7JipzwNfD2pRy6cFv_eP2tM");

Database::connect($_ENV['DB_HOST'],$_ENV['DB_USERNAME'],$_ENV['DB_PASSWORD'],$_ENV['DB_DATABASE']);