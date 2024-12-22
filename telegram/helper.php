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
    return Database::create('users',
    ['uuid','first_name','last_name','username'],
        [$userId,'test 1','test 2','testusername']
    );
}