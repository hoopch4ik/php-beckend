<?php
namespace App\Api\V1\Routes;

use App\Api\V1\Controllers\AuthControllerV1;
use App\Api\V1\Controllers\HomeControllerV1;
use App\Config\ConfigRoutes;
use App\Handlers\Router;



class RouterV1 {
    
    protected static $routing = [];
    protected string $endpoint;



    public function __construct(string $use_route) {

        $router = new Router();
        $router->use($use_route);

        $router->route("get", ConfigRoutes::HEALTH_CHECK, HomeControllerV1::class."::healthCheck");
        $router->route("post", ConfigRoutes::AUTH_LOGIN, AuthControllerV1::class."::login");
        $router->route("post", ConfigRoutes::AUTH_REGISTER, AuthControllerV1::class."::register");
    }

}
