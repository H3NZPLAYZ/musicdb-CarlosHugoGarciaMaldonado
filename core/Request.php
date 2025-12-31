<?php

namespace core;

final class Request
{
    const ROUTES = [
        "home" => "/",
        "login" => "login",
        "register" => "register",
        "logout" => "logout",

        "lists" => "list/all",
        "mylists" => "list/mylists",
        "newlist" => "list/new",
        "showlist" => "list/show",

        "albums" => "album/all",
        "myalbums" => "album/myalbums",
        "newalbum" => "album/new",
        "showalbum" => "album/show",
        "editalbum" => "album/edit",

        "songs" => "song/all",
        "mysongs" => "song/mysongs",
        "newsong" => "song/new",
        "addsong" => "song/add",
        "editsong" => "song/edit",

        "artists" => "artist",

        "manageusers" => "manage/users",
        "newadmin" => "manage/new",
    ];

    private function __construct()
    {}

    public static function isMethod(string $method):bool
    {
        return strtolower($method) === strtolower($_SERVER["REQUEST_METHOD"]);
    }

    public static function get(string $key): ?string
    {
        return $_POST[$key] ?? $_GET[$key] ?? null;
    }

    public static function route(string $name): string
    {
        return "http://" . $_SERVER["HTTP_HOST"] . "/" . self::ROUTES[strtolower($name)];
    }

    public static function redirect(string $url):never
    {
        header("Location: $url");
        exit();
    }

    public static function redirectToRoute(string $name)
    {
        $route = self::route($name);
        self::redirect($route);
        //exit();
    }
}