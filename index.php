<?php
session_start();
require __DIR__ . '/vendor/autoload.php';


use App\Api\V1\Routes\RouterV1;


use App\Middlewares\HttpMiddleware;


// IncludeStyles::setStaticStyles([
    // '/assets/styles/main.css',
// ]);


new HttpMiddleware();
new RouterV1("/api/v1");
