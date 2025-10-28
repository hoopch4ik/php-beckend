<?php
namespace App\Api\V1\Routes;

use App\Config\ConfigRoutes;
use App\Handlers\BaseRouter;



class Router extends BaseRouter {

    public function __construct() {
        parent::__construct("App\\Api\\V1\\Controllers\\");
        parent::prefix("/api/v1");

        parent::route(ConfigRoutes::HOME)->controller("HomeController@healthCheck");


        parent::route(ConfigRoutes::LOGIN)
        ->controller("App\\Forms\\LoginForm@check")
        ->controller("AuthController@login");
        parent::route(ConfigRoutes::REGISTER)
        ->controller("App\\Forms\\RegisterForm@check")
        ->controller("AuthController@register");

    }
}
