<?php
namespace App\Models;

use App\Databases\MysqliDB;
use ValueError;


class Model {
    public function __set($name, $value) {
        throw new ValueError("Not access change variable!");
    }


    public static string $error_message = "";

    public static function disconnect() {
        MysqliDB::getInstance()->disconnect();
    }

    protected static function getDateTime(): string {
        return date('Y-m-d H:i:s');
    }
}