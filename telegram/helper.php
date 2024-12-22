<?php

function getUser($userId): array
{
    return [];
}
function userExists($userId): bool
{
    return true;
}
function createUser($userId): bool
{
    return Database::create('YN_users',
    ['user_id','referral_id','api_token'],
        [$userId,generateString(),generateUUID()]
    );
}
function generateUUID() {
    $uuid = sprintf(
        '%s-%s-%s-%s-%s',
        bin2hex(random_bytes(4)),
        bin2hex(random_bytes(2)),
        bin2hex(random_bytes(2)),
        bin2hex(random_bytes(2)),
        bin2hex(random_bytes(6))
    );

    return $uuid;
}
function generateString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return substr(str_shuffle(str_repeat($characters, ceil($length / strlen($characters)))), 0, $length);
}
