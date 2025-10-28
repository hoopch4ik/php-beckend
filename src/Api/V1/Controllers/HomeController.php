<?php
namespace App\Api\V1\Controllers;

use App\Handlers\ApiResponse;
use App\Handlers\HttpHandler;


class HomeController {

    public static function healthCheck() {
        HttpHandler::$response->setFinish(
            new ApiResponse(
                200,
                true,
                "Api success!"
            )
        );
    }

    public static function isAuthHandler() {
        HttpHandler::$response->setFinish(
            new ApiResponse(
                200,
                true,
                "Вы авторизованы!",
                ["decoded_data"=>HttpHandler::$request->decoded_data]
            )
        );
    }

}
