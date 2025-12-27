<?php

namespace controllers;

use core\Session;
class HomeController extends BaseController
{
    public function home()
    {
        $this->render("home.twig", [
        ]);
    }
}