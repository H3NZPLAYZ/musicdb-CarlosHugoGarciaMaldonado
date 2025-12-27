<?php

namespace controllers;

use core\Database;
use models\Song;
use core\Session;
use core\Request;
use models\Album;
use models\Lists;

use PDO;
class SongController extends BaseController
{
    public function all()
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT * FROM songs");

        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_CLASS, Song::class);

        Session::set("last_action","Show all Songs");

        $this->render("songs/all.twig", [
            "last_action" => Session::get('last_action'),
            "data" => $data
        ]);
    }

    public function new()
    {
        if ( Session::get('userRole') != "artist" ) {
            Request::redirect("home");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {



            Session::set('songName', $_POST['songName'] ?? null);
            Session::set('songAlbum', $_POST['songAlbum'] ?? null);
            Session::set('songMinutes', $_POST['songMinutes'] ?? null);
            Session::set('songSeconds', $_POST['songSeconds'] ?? null);

            $name = Session::get("songName");
            $album = Session::get("songAlbum");
            $minutes = Session::get("songMinutes");
            $seconds = Session::get("songSeconds");

            Session::set('songDuration',$minutes * 60 + $seconds ?? null);

            Song::create();

            $albumCount = Album::getAlbumAmountById(Session::get('userId'));

            Session::set("last_action","New Song");

            $this->render("songs/new.twig", [
                "last_action" => Session::get('last_action'),
                "albumCount" => $albumCount
            ]);

        } else {
            $albumCount = Album::getAlbumAmountById(Session::get('userId'));

            Session::set("last_action","New Song");

            $this->render("songs/new.twig", [
                "last_action" => Session::get('last_action'),
                "albumCount" => $albumCount
            ]);
        }
    }

    public function my()
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT s.song_id,
                                            s.album_id,
                                            s.name,
                                            s.duration
                                        FROM albums a
                                        JOIN songs s ON s.album_id = a.album_id
                                        WHERE a.user_id = :user_id
        ");

        $stmt->execute([
            "user_id"=>Session::get("userId"),
        ]);

        $data = $stmt->fetchAll(PDO::FETCH_CLASS, Song::class);

        Session::set("last_action","My Songs");

        $this->render("songs/my.twig", [
            "last_action"=>Session::get("last_action"),
            "data" => $data
        ]);
    }

    public function add()
    {
        Session::set('add', true);

        if (Session::get('userRole') != "user") {
            Request::redirectToRoute("login");
        }

        $idSong = $_POST['idAdd'] ?? $_GET['idAdd'] ?? Session::get('songId') ?? null;
        $idSong = (int) $idSong;

        if (!$idSong) {
            Request::redirectToRoute("songs");
        }

        Session::set('songId', $idSong);
        $song = Song::getSongById($idSong);

        $songName = $song->getName();

        // Leer el ID de la lista si se ha enviado
        $idList = $_POST['idList'] ?? $_GET['idList'] ?? null;

        if ($idList) {
            $pdo = Database::connect();
            $stmt = $pdo->prepare(
                "INSERT INTO list_song (list_id, song_id) VALUES (:list_id, :song_id)"
            );

            $stmt->execute([
                "list_id" => (int)$idList,
                "song_id" => $idSong
            ]);

            Request::redirect("/song/add?idAdd=".$idSong);
        }

        $lists = Lists::listsWhereNotIn(Session::get("userId"), $idSong);

        $this->render("songs/add.twig", [
            "songName" => $songName,
            "songId" => $idSong,
            "lists" => $lists
        ]);
    }
}