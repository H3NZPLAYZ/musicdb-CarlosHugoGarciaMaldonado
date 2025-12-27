<?php

namespace models;

use core\Database;
use core\Session;
use models\Song;

use PDO;

class Lists
{
    const array PERMANENT_LISTS_NAMES = [
      "Liked",
    ];

    const string LIKED_COVER = "https://image-cdn-ak.spotifycdn.com/image/ab67706c0000da8470d229cb865e8d81cdce0889";
    const string LIKED_DESC = "Here u find all ur liked songs";
    const string DEFAULT_COVER = "https://community.spotify.com/t5/image/serverpage/image-id/196380iDD24539B5FCDEAF9?v=v2";

    private int $list_id;
    private int $user_id;
    private string $name;
    private string $picture_url;
    private string $description;
    private int $public;

    public function getListId() : int
    {
        return $this->list_id;
    }

    public function getUserId() : int
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

    public function getPublic(): int
    {
        return $this->public;
    }

    public static function create(int $user_id,
                                  string $name,
                                  string $picture_url = self::DEFAULT_COVER,
                                  string $description = "",
                                  int $public = 1)
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("INSERT INTO lists (user_id, name, picture_url, description, public)
                                VALUES (:user_id, :name, :picture_url, :description, :public)
        ");

        $stmt->execute([
            ":user_id" => $user_id,
            ":name" => $name,
            ":picture_url" => $picture_url,
            ":description" => $description,
            ":public" => $public
        ]);
    }

    public static function createLiked(int $user_id)
    {
        self::create($user_id, self::PERMANENT_LISTS_NAMES[0], self::LIKED_COVER, self::LIKED_DESC,0);
    }

    public function getListSongAmount() : int
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM list_song WHERE list_id = :list_id");

        $stmt->execute([
            ":list_id" => $this->getListId()
        ]);

        return (int) $stmt->fetchColumn();
    }

    public static function getListAmountById(int $user_id) : int
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT list_id) 
                                        FROM lists 
                                        WHERE user_id = :user_id");

        $stmt->execute([
            'user_id' => $user_id
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function getOwnerName() : string
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT u.name 
                                        FROM users u
                                        INNER JOIN lists ls ON u.user_id = ls.user_id
                                        WHERE ls.user_id = :user_id
        ");

        $stmt->execute([
            "user_id" => $this->getUserId()
        ]);

        return $stmt->fetchColumn();
    }

    public static function listsWhereNotIn(int $user_id, int $song_id) : array
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT l.*
                                        FROM lists l
                                        LEFT JOIN list_song ls ON l.list_id = ls.list_id AND ls.song_id = :song_id
                                        WHERE l.user_id = :user_id
                                        AND ls.song_id IS NULL
        ");

        $stmt->execute([
            'user_id' => $user_id,
            'song_id' => $song_id
        ]);

        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public static function getListById() : Lists
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM lists WHERE list_id = :list_id");

        $stmt->execute([
            "list_id" => Session::get('listId')
        ]);
        return $stmt->fetchObject(self::class);
    }

    public static function getSongsByListId() : array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT s.* 
                                        FROM songs s 
                                        INNER JOIN list_song ls ON ls.song_id = s.song_id
                                        WHERE list_id = :list_id");

        $stmt->execute([
            "list_id" => Session::get('listId')
        ]);

        return $stmt->fetchAll(PDO::FETCH_CLASS, Song::class);
    }

    public function listFormat() : void
    {
        echo '
            <div class="row align-items-center myCard w-50 mx-auto">
                <div class="col-4 text-center">
                    <img class="w-100" src="'.$this->getPictureUrl().'" alt="'.$this->getName().' list image">
                </div>
                <div class="col-8 text-center">
                    <h1><strong>'.$this->getName().'</strong></h1>
                    <p><strong>Owner:</strong> '.$this->getOwnerName().'</p>
                    <p><strong>Privacity:</strong> '.($this->getPublic()?"Public":"Private").'</p>
                    <p><strong>Songs:</strong> '.$this->getListSongAmount().'</p>
                    <p><strong>Description:</strong> '.$this->getDescription().'</p>
                </div>
            </div>
        ';
    }

    public function listFormatAdd() : void
    {
        echo '
            <div class="row align-items-center myCard w-50 mx-auto">
                <div class="col-4 text-center">
                    <img class="w-100" src="'.$this->getPictureUrl().'" alt="'.$this->getName().' list image">
                </div>
                <div class="col-6 text-center">
                    <h1><strong>'.$this->getName().'</strong></h1>
                    <p><strong>Owner:</strong> '.$this->getOwnerName().'</p>                    
                    <p><strong>Privacity:</strong> '.($this->getPublic()?"Public":"Private").'</p>
                    <p><strong>Songs:</strong> '.$this->getListSongAmount().'</p>
                    <p><strong>Description:</strong> '.$this->getDescription().'</p>
                </div>
                <div class="col-2 text-center">
                    <a class="btn btn-dark ms-auto" href="/song/add?idList='.$this->getListId().'">➕</a>                    
                </div>
            </div>
        ';
    }

    public function listFormatShow() : void
    {
        echo '
            <div class="row align-items-center myCard w-50 mx-auto">
                <div class="col-4 text-center">
                    <img class="w-100" src="'.$this->getPictureUrl().'" alt="'.$this->getName().' list image">
                </div>
                <div class="col-6 text-center">
                    <h1><strong>'.$this->getName().'</strong></h1>
                    <p><strong>Owner:</strong> '.$this->getOwnerName().'</p>                    
                    <p><strong>Privacity:</strong> '.($this->getPublic()?"Public":"Private").'</p>
                    <p><strong>Songs:</strong> '.$this->getListSongAmount().'</p>
                    <p><strong>Description:</strong> '.$this->getDescription().'</p>
                </div>
                <div class="col-2 text-center">
                    <a class="btn btn-dark ms-auto" href="/list/show?idList='.$this->getListId().'">👁️</a>                    
                </div>
            </div>
        ';
    }
}