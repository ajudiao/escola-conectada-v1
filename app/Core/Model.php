<?php
namespace App\Core;

use PDO;

abstract class Model
{
    protected static PDO $db;

    public static function setDB(PDO $database)
    {
        self::$db = $database;
    }
}