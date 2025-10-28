<?php
require __DIR__ . '/vendor/autoload.php';


use App\Handlers\HttpHandler;
use App\Handlers\Logger;
use App\Config\ConfigWeb;
use App\Handlers\Router;
use App\Api\V1\Router\Router as RouterV1;

Dotenv\Dotenv::createImmutable(__DIR__)->load();


use Protected\Cache\BaseCache;
use Protected\Cache\SimpleCache;

// (new SimpleCache())->handleRequest();
new BaseCache();



new HttpHandler();
new Logger();

BaseRouter::globalDispatch([
    new RouterV1()
]);


