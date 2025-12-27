<?php

namespace controllers;

use core\Request;
use core\Session;
use core\Auth;
use models\Lists;
use models\User;

class AuthController extends BaseController
{
    public function login()
    {
        if ( Session::get('add') ) {
            Session::set('add',false);
            $this->render("login.twig", [
                "error" => true,
                "errorMsg" => "add_without_login",
            ]);
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                Session::set('email', strtolower($_POST['email']) ?? null);
                Session::set('password', $_POST['password'] ?? null);

                $email    = Session::get('email');
                $password = Session::get('password');

                if (!$email || !$password) {
                    $this->render("login.twig", [
                        "error" => true,
                        "errorMsg" => "missing_fields"
                    ]);
                } else {
                    $login = Auth::login($email, $password);

                    if (!$login) {
                        $this->render("login.twig", [
                            "error" => true,
                            "errorMsg" => "incorrect_password"
                        ]);
                    } else {
                        $user = Auth::user();

                        Session::set('userId', $user->getId());
                        Session::set('userName', $user->getName());
                        Session::set('userRole', $user->getRole());

                        $this->render("login.twig", [
                            "success" => true]);
                    }
                }
            } else {
                $this->render("login.twig", [
                    "last_action" => Session::get("last_action"),
                ]);
            }
        }
    }

    public function register() : void
    {
        if ( Session::get('userName') != null ) {
            Request::redirect("home");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            Session::set('name', $_POST['name'] ?? null);
            Session::set('email', strtolower($_POST['email']) ?? null);
            Session::set('password', $_POST['password'] ?? null);
            Session::set('role', $_POST['role'] ?? null);

            $name = Session::get('name');
            $email    = Session::get('email');
            $password = Session::get('password');
            $role     = Session::get('role');

            if (!$name || !$email || !$password || !$role) {
                 $this->render("register.twig", [
                    "error" => true,
                    "errorMsg" => "missing_fields"
                ]);
            } else {
                $result = User::create();

                $user = User::getByEmailAndPassword($email, $password);

                if ($user->getRole() == "user") {
                    Lists::createLiked($user->getId());
                }

                if ($result === true) {
                    $this->render("register.twig", [
                        "success" => true
                    ]);
                } else {
                    $this->render("register.twig", [
                        "error" => true,
                        "errorMsg" => $result
                    ]);
                }
            }
        } else {
            $this->render("register.twig", [
                "last_action" => Session::get("last_action"),
            ]);
        }
    }

    public function logout() : void
    {
        Session::destroy();
    }
}