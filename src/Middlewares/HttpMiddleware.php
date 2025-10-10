<?php

namespace App\Middlewares;

use App\Middlewares\HttpMiddleware\Request;


class HttpMiddleware extends Request {
    public static Request $request;

    public function __construct() {
        HttpMiddleware::$request = new Request();
    }
}

