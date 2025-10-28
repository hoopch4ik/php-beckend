<?php
namespace App\Handlers\JwtHandlers;

use App\Utils\MyJwt;
use App\Interfaces\Base\IUserRole;
use stdClass;


class CreatorJWT {

    public static function generate(): string {
        return MyJwt::encode(["role"=>IUserRole::CREATOR], null);
    }

    public static function check(string $bearer): array|stdClass {
        return MyJwt::decode($bearer);
    }
}

