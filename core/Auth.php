<?php

namespace core;

use core\Session;
use models\User;

final class Auth
{
    public static function login(string $email, string $password):bool
    {
        $user = User::getByEmailAndPassword($email, $password);

        if (is_object($user))
        {
            Session::start($user);
        }

        return is_object($user);
    }

    public static function user(): User|false
    {
        $returnable = false;

        if (Session::active())
        {
            $returnable = User::getById(Session::get("id"));
        }

        return $returnable;
    }
}