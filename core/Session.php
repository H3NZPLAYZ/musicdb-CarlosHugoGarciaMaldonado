<?php

namespace core;

use models\User;

final class Session
{
    const MAX_AGE = 18000; // 5 HORAS

    private static function init():void
    {
        if (session_status() == 1)
        {
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        self::init();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key): mixed
    {
        self::init();
        $returnable = null;
        if (isset($_SESSION[$key]))
        {
            $returnable = $_SESSION[$key];
        }
        return $returnable;
    }

    public static function update() : void
    {
        self::set("time", time());
    }

    public static function start(User $user): void
    {
        self::set("id", $user->getId());
        self::update();
    }

    public static function active():bool
    {
        return (session_status() == 2) &&
                (self::get("id")) &&
                ((time() - self::get("time")) <= self::MAX_AGE);
    }

    public static function destroy():void
    {
        $_SESSION = [];
        session_destroy();
        Request::redirectToRoute("home");
    }
}