<?php

include_once("telegram.php");
include_once("helper.php");

Telegram::setToken("api_key");
$updates = Telegram::Updates();