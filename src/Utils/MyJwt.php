<?php
namespace App\Utils;

use App\Config\ConfigKeys;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use stdClass;


class MyJwt {

    public static function encode(array $data, int $time_minute=10): string {
        $time = time();
        $payload = [
            'data' => $data,
            'iat' => $time,
            'exp' => $time_minute * 60 + $time
        ];

        return JWT::encode($payload, ConfigKeys::SECRET_KEY, 'HS256');
    }

    public static function decode(string $jwt): stdClass {
        $decoded_data = JWT::decode($jwt, new Key(ConfigKeys::SECRET_KEY, 'HS256'));
        
        $data = $decoded_data->data;
        // $iat = $decoded_data["iat"];
        $exp = $decoded_data->exp;

        $time = time();

        if ($exp <= $time) {
            throw new ExpiredException("Token is expired((");
        }
        return $data;
    }
}