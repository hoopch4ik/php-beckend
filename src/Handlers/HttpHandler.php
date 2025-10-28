<?php
namespace App\Handlers;

use App\Handlers\HttpHandler\Request;
use App\Handlers\HttpHandler\Response;


class HttpHandler {
    public static Request $request;
    public static Response $response;

    public function __construct() {
        HttpHandler::$request = new Request();
        HttpHandler::$response = new Response();
    }
}

