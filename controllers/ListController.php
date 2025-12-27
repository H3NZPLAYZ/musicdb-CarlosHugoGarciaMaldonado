<?php

namespace controllers;

use core\Database;
use core\Session;
use models\Lists;
use core\Request;

use PDO;

class ListController extends BaseController
{
    public function all()
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT * FROM lists WHERE public = 1");

        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_CLASS, Lists::class);

        Session::set("last_action","Show all Lists");

        $this->render("lists/all.twig", [
            "last_action" => Session::get('last_action'),
            "data" => $data
        ]);
    }

    public function new()
    {
        if ( Session::get('userRole') != "user" ) {
            Request::redirect("home");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            Session::set('listName', $_POST['listName'] ?? null);
            Session::set('listPicture_url', $_POST['listPicture_url'] ?? null);
            Session::set('listDescription', $_POST['listDescription'] ?? null);
            Session::set('public', $_POST['public'] ?? 0);

            $name = Session::get("listName");
            $picture = Session::get("listPicture_url");
            $description = Session::get("listDescription");
            $public = Session::get("public");

            if ($name == null)
            {
                $name = "New List ".((string) Lists::getListAmountById(Session::get("userId")));
            }

            if ($picture == null)
            {
                $picture = Lists::DEFAULT_COVER;
            }

            if ($description == null)
            {
                $description = "None";
            }

            Lists::create(Session::get("userId"),$name, $picture, $description, $public);


            $this->render("lists/new.twig", [
                "last_action"=>Session::get("last_action"),
                "success" => true
            ]);


        } else {
            Session::set("last_action","New List");

            $this->render("lists/new.twig", [
                "last_action"=>Session::get("last_action")
            ]);
        }
    }

    public function my()
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT * 
                                        FROM lists 
                                        WHERE user_id = :user_id
        ");

        $stmt->execute([
            "user_id"=>Session::get("userId"),
        ]);

        $data = $stmt->fetchAll(PDO::FETCH_CLASS, Lists::class);

        Session::set("last_action","My Lists");

        $this->render("lists/my.twig", [
            "last_action"=>Session::get("last_action"),
            "data" => $data
        ]);
    }

    public function show()
    {
        $idList = $_POST['idList'] ?? $_GET['idList'] ?? Session::get('idList') ?? null;
        $idList = (int) $idList;

        if (!$idList) {
            Request::redirectToRoute("lists");
        }

        Session::set('listId', $idList);
        $list = Lists::getListById();

        $songsInList = $list->getSongsByListId();

        $userRole = Session::get("userRole");

        $this->render("lists/show.twig", [
            "last_action"=>Session::get("last_action"),
            "userRole" => $userRole,
            "listName" => $list->getName(),
            "songsInList" => $songsInList
        ]);
    }


}