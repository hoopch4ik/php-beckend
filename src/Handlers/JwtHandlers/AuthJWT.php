<?php
namespace App\Handlers\JwtHandlers;

use App\Utils\MyJwt;
use stdClass;


class AuthJWT {


    public static function generate(array $data): string {
        return MyJwt::encode($data, 60*24);
    }

    public static function check(string $bearer): array|stdClass {
        return MyJwt::decode($bearer);
    }
}

