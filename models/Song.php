<?php

namespace models;

use core\Database;
use core\Session;
use PDO;

class Song
{
    private int $song_id;
    private int $album_id;
    private string $name;
    private string $duration;

    public function getId(): int
    {
        return $this->song_id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAlbumId(): int
    {
        return $this->album_id;
    }

    public function getFormattedDuration(): string
    {
        $minutes = (int)($this->duration/60);
        $seconds = $this->duration % 60;

        return sprintf("%d:%02d", $minutes, $seconds);
    }

    public static function getSongById(int $song_id) : Song
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT * FROM songs WHERE song_id = :song_id");

        $stmt->execute([
            'song_id' => $song_id
        ]);

        return $stmt->fetchObject( Song::class);
    }

    public static function getSongCountByArtistId(int $user_id): int
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("
        SELECT COUNT(s.song_id) AS total_songs
        FROM songs s
        JOIN albums a ON s.album_id = a.album_id
        WHERE a.user_id = :user_id
    ");

        $stmt->execute([
            'user_id' => $user_id
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function songFormat() : void
    {
        $album = Album::getAlbumById($this->getAlbumId());

        echo '
                <div class="row align-items-center myCard w-50 mx-auto">
                    <div class="col-2 text-center">
                        <img class="w-100" src="'.$album->getPictureUrl().'" alt="'.$album->getName().' cover">
                    </div>
                    <div class="col-10 text-center">
                        <h1><strong>'.$this->getName().'</strong></h1>
                        <p><strong>Artist:</strong> '.$album->getAlbumArtistName().' <strong>Album:</strong> '.$album->getName().'</p>
                        <p><strong>Duration:</strong> '.$this->getFormattedDuration().'</p>
                    </div>
                </div>
        ';
    }

    public function songFormatAdd() : void
    {
        $album = Album::getAlbumById($this->getAlbumId());

        echo '
                <div class="row align-items-center myCard w-50 mx-auto">
                    <div class="col-2 text-center">
                        <img class="w-100" src="'.$album->getPictureUrl().'" alt="'.$album->getName().' cover">
                    </div>
                    <div class="col-8 text-center">
                        <h1><strong>'.$this->getName().'</strong></h1>
                        <p><strong>Artist:</strong> '.$album->getAlbumArtistName().' <strong>Album:</strong> '.$album->getName().'</p>
                        <p><strong>Duration:</strong> '.$this->getFormattedDuration().'</p>
                    </div>
                    <div class="col-2 text-center">
                        <a class="btn btn-dark ms-auto" href="/song/add?idAdd='.$this->getId().'">➕</a>
                    </div>
                </div>
        ';
    }

    public static function create()
    {
        $pdo = Database::connect() ;
        $stmt = $pdo->prepare("INSERT INTO songs(album_id, name, duration)
                                    VALUES (:album_id, :name, :duration)"
        );

        $stmt->execute([
            "album_id" => Session::get("songAlbum"),
            "name" => Session::get("songName"),
            "duration" => Session::get("songDuration")
        ]);
    }
}