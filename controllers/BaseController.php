<?php

namespace controllers;

use core\Request;
use core\Session;
use models\Album;

abstract class BaseController
{
    private \Twig\Environment $twig;

    public function __construct()
    {
        require_once "./vendor/autoload.php";

        $loader = new \Twig\Loader\FilesystemLoader("./views");

        $this->twig = new \Twig\Environment($loader);

        $this->twig->addFunction( new \Twig\TwigFunction( "active",
            function()
            {
                return Session::active();
            }));

        $this->twig->addFunction( new \Twig\TwigFunction( "route",
            function($name)
            {
                    return Request::route($name);
            }));

        $this->twig->addFunction( new \Twig\TwigFunction( "getAlbumsByUserId",
        function($user_id)
        {
            return Album::getAlbumsByUserId($user_id);
        }));

        //TEST
        $this->twig->addGlobal('year', date("Y"));

        $this->twig->addGlobal('userId', Session::get('userId') ?? 'None');
        $this->twig->addGlobal('userName', Session::get('userName') ?? 'None');
        $this->twig->addGlobal('userRole', (Session::get('userRole'))? ucfirst(Session::get('userRole')) : 'Guest');
        $this->twig->addGlobal('last_action', Session::get('last_action') ?? 'No actions yet.');

    }

    public function render(string $view, array $params = []): void
    {
        echo $this->twig->render($view, $params);
    }

}