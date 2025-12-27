<?php

namespace controllers;

use core\Database;
use core\Request;
use core\Session;
use models\User;

use PDO;

class ManageController extends BaseController
{
    public function users()
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT * FROM users WHERE NOT user_id = :user_id");

        $stmt->execute([
            ":user_id" => Session::get("userId")
        ]);

        $data = $stmt->fetchAll(PDO::FETCH_CLASS, User::class);

        Session::set("last_action","Manage users");

        $this->render("manage/users.twig", [
            "last_action"=>Session::get("last_action"),
            "data" => $data
        ]);
    }

    public function new()
    {
        if ( Session::get('userRole') !== "admin" ) {
            Request::redirectToRoute("home");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            Session::set('name', $_POST['name'] ?? null);
            Session::set('email', strtolower($_POST['email']) ?? null);
            Session::set('password', $_POST['password'] ?? null);
            Session::set('role', Session::get('userRole') ?? null);

            $result = User::create();

            if ($result === true) {
                $this->render("manage/new.twig", [
                    "last_action" => Session::get("last_action"),
                    "success" => true
                ]);
            } else {
                $this->render("manage/new.twig", [
                    "last_action" => Session::get("last_action"),
                    "error" => true,
                    "errorMsg" => $result
                ]);
            }

        } else {
            Session::set('last_action', 'Admin Creation');

            $this->render("manage/new.twig", [
                "last_action" => Session::get("last_action"),
            ]);
        }
    }

    public function del()
    {
        if (Session::get("userRole") != "admin")
        {
            Request::redirectToRoute("home");
        }

        $id = isset($_GET['idDel']) ? (int)$_GET['idDel'] : null;

        if (!$id) {
            Request::redirectToRoute("manageusers");
        }

        User::del($id);

        Request::redirectToRoute("manageusers");
    }
}