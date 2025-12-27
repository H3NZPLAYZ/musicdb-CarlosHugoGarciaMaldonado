<?php

namespace controllers;

use core\Database;
use core\Session;
use models\User;

use PDO;
class ArtistController extends BaseController
{
    public function all()
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'artist'");

        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_CLASS, User::class);

        Session::set("last_action","Show all Artists");

        $this->render("artists/all.twig", [
            "last_action" => Session::get('last_action'),
            "data" => $data,
        ]);
    }
}