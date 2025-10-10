<?php
namespace App\Middlewares;

use App\Data\SessionData;
use App\Handlers\ApiResponse;
use App\Interfaces\Base\IUserRole;
use App\Utils\MyJwt;
use Exception;

class AuthMiddleware {


    public static function isAuthApi() {
        $bearer = HttpMiddleware::$request->headers["Bearer"] ?? "not_token";

        try {
            $decoded_data = MyJwt::decode($bearer);
            HttpMiddleware::$request->setDecodedData($decoded_data);
        } catch (Exception $e) {
            new ApiResponse(
                401,
                false,
                "Your token expired or invalid!"
            );
        }
    }
    
    public static function isAuthAdminApi() {
        $bearer = HttpMiddleware::$request->headers["Bearer"] ?? "not_token";

        try {
            $decoded_data = MyJwt::decode($bearer);
            HttpMiddleware::$request->setDecodedData($decoded_data);


            if ($decoded_data->role != IUserRole::ADMIN) {
                new ApiResponse(
                    401,
                    false,
                    "Доступ запрещён!"
                );
            }
        } catch (Exception $e) {
            new ApiResponse(
                401,
                false,
                "Ваш токен истёк или неверный!"
            );
        }
    }

}
