<?php
namespace App\Api\V1\Router;

use App\Config\ConfigRoutes;
use App\Handlers\BaseRouter;



class Router extends BaseRouter {

    public function __construct() {
        parent::__construct("App\\Api\\V1\\Controllers\\");
        parent::prefix("/api/v1");

        parent::get(ConfigRoutes::HEALTH_CHECK)->controller("HomeController@healthCheck");


        parent::post(ConfigRoutes::AUTH_LOGIN)
        ->controller("App\\Forms\\LoginForm@check")
        ->controller("AuthController@login");
        parent::post(ConfigRoutes::AUTH_REGISTER)
        ->controller("App\\Forms\\RegisterForm@check")
        ->controller("AuthController@register");

    }
}
