<?php

namespace App\Handlers;

use App\Handlers\HttpHandler;
use App\Config\ConfigWeb;
use App\Handlers\ApiResponse;


class Router {
    protected static array $routers = [];
    protected static array $full_routes = [];

    protected array $endpoints;
    protected string $use_route;



    public function __construct(...$routers) {
        header("Content-Type: application/json; charset=utf-8");

        if (!ConfigWeb::IS_OPEN_API) {
            new ApiResponse(
                404,
                false,
                "Api closed!"
            );
        }
        
        self::$routers = $routers;
        $this->use_route = "/";
        $this->endpoints = [];
    }

    public static function handleRouters() {
        foreach (self::$routers as $router) {
            $router->handleRoutes();
        }
    }

    /**
     *
     * @param string $use_route
     * @param string[]|null $controllers
     * @return void
     */
    public function use(string $use_route, ...$controllers) {
        $this->use_route = $use_route;
        
        if ($controllers) {
            foreach ($controllers as $controller) {
                call_user_func($controller);
            }
        }
    }

    /**
     * @param "get"|"post" $method
     * @param string $route
     * @param string[]|null $controllers
     * @return void
     */
    public function route(string $method, string $route, ...$controllers) {
        $path = $this->use_route == "/" ? $route : $this->use_route.$route;
        $this->endpoints[$method."::".$route] = [
            "controllers"=>$controllers
        ];
        self::$full_routes[$method."::".$path] = [
            "controllers"=>$controllers
        ];
    }

    public function handleRoutes() {
        if (!str_starts_with(HttpHandler::$request->route, $this->use_route)) {
            $this->notFound();
            return;
        }

        if (empty(self::$full_routes[strtolower(HttpHandler::$request->method)."::".HttpHandler::$request->route])) {
            $this->notFound();
            return;
        }

        $handleRoute = self::$full_routes[strtolower(HttpHandler::$request->method)."::".HttpHandler::$request->route];


        if ($handleRoute["controllers"]) {
            foreach ($handleRoute["controllers"] as $controller) {
                call_user_func($controller);
            }
        } else {
            // Сюда не должно попадать
            new ApiResponse(
                404,
                false,
                "Метод контроллера не найден!"
            );
        }
    }

    protected function notFound() {
        new ApiResponse(
            404,
            false,
            "Not Found"
        );
    }
    
    protected function notAllowed() {
        new ApiResponse(
            403,
            false,
            "Method Not Allowed"
        );
    }
}