<?php
namespace App\Api\V1\Controllers;

use App\Handlers\ApiResponse;
use App\Middlewares\HttpMiddleware;


class HomeControllerV1 {

    public static function healthCheck() {
        new ApiResponse(
            200,
            true,
            "Api success!"
        );
    }

    public static function isAuthHandler() {
        new ApiResponse(
            200,
            true,
            "Вы авторизованы!",
            ["decoded_data"=>HttpMiddleware::$request->decoded_data]
        );
    }

}
