<?php

namespace models;

use core\Database;
use core\Session;
use PDO;
use PDOException;

class Album
{
    const string DEFAULT_COVER = "https://upload.wikimedia.org/wikipedia/commons/3/3c/No-album-art.png";
    private int $album_id;
    private int $user_id;
    private string $name;
    private string $picture_url;

    private string $description;

    private int $year;

    public function getId(): int
    {
        return $this->album_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPictureUrl(): string
    {
        return $this->picture_url;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getYear(): int
    {
        return $this->year;
    }


    public static function create(
        ?int $user_id = null,
        ?string $albumName = null,
        ?string $picture_url = null,
        ?string $albumDescription = null,
        ?string $year = null
    ) : mixed
    {
        if ($user_id === null) {
            $user_id = Session::get("userId");
        }

        if (empty(trim($albumName ?? ''))) {
            $albumName = "New album ".(self::getAlbumAmountById($user_id)+1);
        }

        if (empty(trim($albumDescription ?? ''))) {
            $albumDescription = "New album ".(self::getAlbumAmountById($user_id)+1);
        }

        if (empty(trim($picture_url ?? ''))) {
            $picture_url = self::DEFAULT_COVER;
        }

        if (!is_numeric($year) || $year === null || trim($year) === '') {
            $year = (int) date("Y");
        } else {
            $year = (int) $year;
        }

        try {
            $pdo = Database::connect();
            $stmt = $pdo->prepare("INSERT INTO albums (user_id, name, picture_url, description, year)
                                        VALUES (:user_id, :name, :picture_url, :description, :year)
            ");

            $stmt->execute([
                ":user_id" => $user_id,
                ":name" => $albumName,
                ":picture_url" => $picture_url,
                ":description" => $albumDescription,
                ":year" => $year
            ]);

            return true;

        } catch (PDOException $e) {

            if ($e->getCode() == 23000) {
                return 'duplicate_album';
            }
            return 'db_error';
        }
    }

    public static function del(int $albumId)
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("DELETE FROM albums WHERE album_id = :albumId");

        $stmt->execute([
            ":albumId" => $albumId
        ]);
    }

    public static function update()
    {
        $pdo = Database::connect() ;
        $stmt = $pdo->prepare("UPDATE albums
                                        SET user_id = :user_id,
                                            name = :name, 
                                            picture_url = :picture_url,
                                            description = :description,
                                            year = :year
                                        WHERE album_id = :album_id"
        );

        $stmt->execute([
            "user_id" => Session::get("userId"),
            "name" => Session::get("albumName"),
            "picture_url" => Session::get("albumPicture_url"),
            "description" => Session::get("albumDescription"),
            "year" => Session::get("albumYear"),
            "album_id" => Session::get("albumId")
        ]);
    }

    public static function getAlbumAmountById(int $user_id) : int
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT album_id) 
                                        FROM albums 
                                        WHERE user_id = :user_id");

        $stmt->execute([
            'user_id' => $user_id
        ]);

        return (int) $stmt->fetchColumn();
    }

    public static function getAlbumById(int $album_id) : mixed
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT * 
                                        FROM albums
                                        WHERE album_id = :album_id
        ");

        $stmt->execute([
            'album_id' => $album_id
        ]);

        return $stmt->fetchObject(Album::class);
    }

    public function getAlbumArtistName() : string
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT name 
                                        FROM users 
                                        WHERE user_id = :user_id");

        $stmt->execute([
            'user_id' => $this->getUserId()
        ]);

        return $stmt->fetch()[0];
    }

    public static function getAlbumsByUserId(int $user_id) : array
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT *
                                        FROM albums
                                        WHERE user_id = :user_id
        ");

        $stmt->execute([
            'user_id' => $user_id
        ]);

        return $stmt->fetchAll(PDO::FETCH_CLASS, Album::class);
    }

    public function getAlbumSongAmount() : int
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT song_id) 
                                        FROM songs
                                        WHERE album_id = :album_id
        ");

        $stmt->execute([
            'album_id' => $this->getId()
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function getSongsByAlbumId() : array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM songs WHERE album_id = :album_id");

        $stmt->execute([
            'album_id' => $this->album_id
        ]);

        return $stmt->fetchAll(PDO::FETCH_CLASS, Song::class);
    }

    public function albumFormat() : void
    {
        echo '
            <div class="row align-items-center myCard w-50 mx-auto">
                <div class="col-4 text-center">
                    <img class="w-100" src="'.$this->getPictureUrl().'" alt="'.$this->getName().' cover">
                </div>
                <div class="col-8 text-center">
                    <h1><strong>'.$this->getName().'</strong></h1>
                    <p><strong>Artist:</strong> '.$this->getAlbumArtistName().
                    ' <strong>Album:</strong> '.$this->getName().'</p>
                    <p><strong>Year:</strong> '.$this->getYear().
                    ' <strong>Songs:</strong> '.$this->getAlbumSongAmount().'</p>
                    <p><strong>Description:</strong> '.$this->getDescription().'</p>
                </div>
            </div>
        ';
    }

    public function albumFormatShow() : void
    {
        echo '
            <div class="row align-items-center myCard w-50 mx-auto">
                <div class="col-3 text-center">
                    <img class="w-100" src="'.$this->getPictureUrl().'" alt="'.$this->getName().' cover">
                </div>
                <div class="col-7 text-center">
                    <h1><strong>'.$this->getName().'</strong></h1>
                    <p><strong>Artist:</strong> '.$this->getAlbumArtistName().
            ' <strong>Album:</strong> '.$this->getName().'</p>
                    <p><strong>Year:</strong> '.$this->getYear().
            ' <strong>Songs:</strong> '.$this->getAlbumSongAmount().'</p>
                    <p><strong>Description:</strong> '.$this->getDescription().'</p>
                </div>
                <div class="col-2 text-center">
                    <a class="btn btn-dark ms-auto" href="/album/show?idAlbum='.$this->getId().'">👁️</a>
                </div>
            </div>
        ';
    }

    public function albumFormatShowEditDelete() : void
    {
        echo '
            <div class="row align-items-center myCard w-50 mx-auto">
                <div class="col-3 text-center">
                    <img class="w-100" src="'.$this->getPictureUrl().'" alt="'.$this->getName().' cover">
                </div>
                <div class="col-7 text-center">
                    <h1><strong>'.$this->getName().'</strong></h1>
                    <p><strong>Artist:</strong> '.$this->getAlbumArtistName().
            ' <strong>Album:</strong> '.$this->getName().'</p>
                    <p><strong>Year:</strong> '.$this->getYear().
            ' <strong>Songs:</strong> '.$this->getAlbumSongAmount().'</p>
                    <p><strong>Description:</strong> '.$this->getDescription().'</p>
                </div>
                <div class="col-2 text-center">
                    <a class="btn btn-dark ms-auto mb-2" href="/album/show?idAlbum='.$this->getId().'">👁️</a>
                    <a class="btn btn-dark ms-auto mb-2" href="/album/edit?idAlbum='.$this->getId().'">✏️️</a>
                    <a class="btn btn-dark ms-auto mb-2" href="/album/del?idAlbum='.$this->getId().'">🗑️</a>
                </div>
            </div>
        ';
    }
}