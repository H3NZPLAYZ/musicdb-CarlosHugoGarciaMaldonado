<?php

namespace models;

use core\Database;
use core\Request;
use core\Session;

use PDOException;

class User
{
    private int $user_id;
    private string $name;
    private string $email;
    private string $password;
    private string $created_at;
    private string $role;

    public function getId(): int
    {
        return $this->user_id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    private function checkPassword(string $password):bool
    {
        return $password == $this->password;
    }

    public static function getByEmailAndPassword(string $email, string $password): User|false
    {
        $returnable = false;

        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND password = :password");
        $stmt->execute(["email" => $email, "password" => $password]);

        $user = $stmt->fetchObject(User::class);

        if (is_object($user)) {
            if ($user->checkPassword($password)) {
                $returnable = $user;
            }
        }

        return $returnable;
    }

    public static function getById(int $id): User|false
    {
        $pdo = Database::connect() ;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :id") ;
        $stmt->execute([ ":id" => Session::get("id") ]) ;

        return $stmt->fetchObject(User::class) ;
    }

    public static function getNameById(int $id): string
    {
        $user = self::getById($id);
        return $user->getName();
    }

    public static function del(int $id): void
    {
        $pdo = Database::connect() ;
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = :id") ;
        $stmt->execute([
            ":id" => $id
        ]);

        Request::route("manageusers");
    }

    public static function create() : mixed
    {
        try {
            $pdo = Database::connect() ;
            $stmt = $pdo->prepare("INSERT INTO users(name, email, password, role)
                                    VALUES (:name, :email, :password, :role)"
            );

            $stmt->execute([
                "name" => Session::get("name"),
                "email" => Session::get("email"),
                "password" => Session::get("password"),
                "role" => Session::get("role")
            ]);

            return true;

        } catch (PDOException $e) {

            if ($e->getCode() == 23000) {
                return 'duplicate_email';
            }
            return 'db_error';
        }
    }

    public function artistFormat() : void
    {
        echo '
                <div class="row align-items-center myCard w-50 mx-auto">
                    <div class="col text-center">
                        <h1><strong>'.$this->getName().'</strong></h1>
                        <p>Albums: '.Album::getAlbumAmountById($this->getId()).'</p>
                        <p>Songs: '.Song::getSongCountByArtistId($this->getId()).'</p>
                    </div>
                </div>
        ';
    }

    public function userManage() : void
    {
        echo '
            <div class="d-flex align-items-center myCard w-25 mx-auto gap-3 text-center">
                <p class="mb-0"><strong>Email: </strong>'.$this->getEmail().'</p>
                <p class="mb-0"><strong>Role: </strong>'.ucfirst($this->getRole()).'</p>
                <a class="btn btn-danger ms-auto" href="/manage/del/'.$this->getId().'">🗑️</a>
            </div>
        ';
    }



}