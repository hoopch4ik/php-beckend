<?php

namespace App\Handlers;

use App\Middlewares\HttpMiddleware;


class Router {
    protected string $use_route;



    public function __construct(...$routers) {
        $this->use_route = "/";
        $this->setHeader("Content-Type: application/json; charset=utf-8");

        
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

        if (!str_starts_with(HttpMiddleware::$request->route, $this->use_route)) {
            return;
        }

        $path = $this->use_route == "/" ? $route : $this->use_route.$route;
        if (HttpMiddleware::$request->route != $path) {
            return;
        }

        if (strtolower(HttpMiddleware::$request->method) != $method) {
            return;
        }

        if ($controllers) {
            foreach ($controllers as $controller) {
                call_user_func($controller);
            }
        }
    }

    public function setHeader(string $header) {
        header($header);
    }
}