<?php

namespace core;

use PDOException;

final class Database
{
    private const DB_HOST = "db";
    private const DB_NAME = "music";

    private const DB_USER = "root";
    private const DB_PASS = "root";

    private function __clone(): void
    {}

    private function __construct()
    {}

    /**
     * @return \PDO\Mysql|null
     */
    public static function connect(): ?\PDO\Mysql
    {
        try {
            $dsn = "mysql:host=" . self::DB_HOST . ";dbname=" . self::DB_NAME . ";charset=utf8mb4";
            return \PDO::connect($dsn, self::DB_USER, self::DB_PASS);
        } catch (PDOException $e) {
            die("ERROR ". $e->getMessage());
        }
    }
}