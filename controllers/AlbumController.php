<?php

namespace controllers;

use core\Database;
use core\Session;
use core\Request;
use models\Album;

use PDO;

class AlbumController extends BaseController
{
    public function all()
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT * FROM albums");

        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_CLASS, Album::class);

        Session::set("last_action","Show all Albums");

        $this->render("albums/all.twig", [
            "last_action"=>Session::get("last_action"),
            "data" => $data
        ]);
    }

    public function new()
    {
        if ( Session::get('userRole') != "artist" ) {
            Request::redirect("home");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            Session::set('albumName', $_POST['albumName'] ?? null);
            Session::set('albumPicture_url', $_POST['albumPicture_url'] ?? null);
            Session::set('albumDescription', $_POST['albumDescription'] ?? null);
            Session::set('albumYear', $_POST['albumYear'] ?? null);

            $name = Session::get("albumName");
            $picture = Session::get("albumPicture_url");
            $description = Session::get("albumDescription");
            $year = Session::get("albumYear");

            $result = Album::create(Session::get("userId"),$name, $picture, $description, $year);

            if ($result === true) {
                $this->render("albums/new.twig", [
                    "last_action"=>Session::get("last_action"),
                    "success" => true
                ]);
            } else {
                $this->render("albums/new.twig", [
                    "last_action"=>Session::get("last_action"),
                    "error" => true,
                    "errorMsg" => $result
                ]);
            }

        } else {
            Session::set("last_action","New Album");

            $this->render("albums/new.twig", [
                "last_action"=>Session::get("last_action")
            ]);
        }
    }

    public function my()
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT * 
                                        FROM albums 
                                        WHERE user_id = :user_id
        ");

        $stmt->execute([
            "user_id"=>Session::get("userId"),
        ]);

        $data = $stmt->fetchAll(PDO::FETCH_CLASS, Album::class);

        Session::set("last_action","My Albums");

        $this->render("albums/my.twig", [
            "last_action"=>Session::get("last_action"),
            "data" => $data
        ]);
    }

    public function show()
    {
        $idAlbum = $_POST['idAlbum'] ?? $_GET['idAlbum'] ?? Session::get('idAlbum') ?? null;
        $idAlbum = (int) $idAlbum;

        if (!$idAlbum) {
            Request::redirectToRoute("albums");
        }

        Session::set('albumId', $idAlbum);
        $album = Album::getAlbumById($idAlbum);

        $songsInAlbum = $album->getSongsByAlbumId();

        $userRole = Session::get("userRole");

        $this->render("albums/show.twig", [
           "last_action"=>Session::get("last_action"),
           "userRole"=>$userRole,
           "albumName" => $album->getName(),
           "songsInAlbum" => $songsInAlbum
        ]);
    }

    public function del()
    {
        if (Session::get("userRole") != "artist")
        {
            Request::redirectToRoute("home");
        }

        $idAlbum = $_POST['idAlbum'] ?? $_GET['idAlbum'] ?? Session::get('idAlbum') ?? null;
        $idAlbum = (int) $idAlbum;

        if (!$idAlbum) {
            Request::redirectToRoute("myalbums");
        }

        Album::del($idAlbum);

        Request::redirectToRoute("myalbums");
    }

    public function edit()
    {
        if (Session::get("userRole") != "artist")
        {
            Request::redirectToRoute("home");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            Session::set('albumName', $_POST['albumName'] ?? null);
            Session::set('albumPicture_url', $_POST['albumPicture_url'] ?? null);
            Session::set('albumDescription', $_POST['albumDescription'] ?? null);
            Session::set('albumYear', $_POST['albumYear'] ?? null);

            Album::update();

            Session::set("last_action","Album Update");

            Request::redirectToRoute("myalbums");
        }

        $idAlbum = $_POST['idAlbum'] ?? $_GET['idAlbum'] ?? Session::get('idAlbum') ?? null;
        $idAlbum = (int) $idAlbum;

        if (!$idAlbum) {
            Request::redirectToRoute("myalbums");
        }

        $album = Album::getAlbumById($idAlbum);
        Session::set('albumId', $idAlbum);

        $this->render("albums/edit.twig", [
            "album" => $album,
        ]);
    }


}