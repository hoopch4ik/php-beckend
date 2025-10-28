<?php

namespace App\Handlers;

use App\Config\ConfigWeb;
use App\Handlers\HttpHandler;
use App\Handlers\ApiResponse;


class BaseRouter {
    private array $routes;
    private string|null $currentRoute;
    protected string $controllers_path;
    protected string $current_method;
    protected string $current_uri;
    protected string $prefix;


    public function __construct(string|null $controllers_path=null) {
        $this->routes = [];
        $this->currentRoute = null;
        $this->controllers_path = $controllers_path ?? "";
        $this->current_method = "";
        $this->current_uri = "";
        $this->prefix = "/";
    }


    /**
    * Добавляет префикс вперёд всех маршрутов
    * 
    * @param string $prefix Префикс
    * @return self
    */
    public function prefix(string $prefix) {
        $this->prefix = $prefix;
    }

    /**
     * Добавляет GET маршрут.
     *
     * @param string $uri       URI маршрута.
     * @param mixed  $callback  Функция обратного вызова или строка вида 'Controller@method'.
     */
    public function get(string $uri): self
    {
        $this->addRoute('GET', $uri);

        return $this;
    }

    /**
     * Добавляет POST маршрут.
     *
     * @param string $uri       URI маршрута.
     * @param mixed  $callback  Функция обратного вызова или строка вида 'Controller@method'.
     */
    public function post(string $uri): self
    {
        $this->addRoute('POST', $uri);

        return $this;
    }

    /**
     * Добавляет PUT маршрут.
     *
     * @param string $uri       URI маршрута.
     * @param mixed  $callback  Функция обратного вызова или строка вида 'Controller@method'.
     */
    public function put(string $uri): self
    {
        $this->addRoute('PUT', $uri);

        return $this;
    }

    /**
     * Добавляет PATCH маршрут.
     *
     * @param string $uri       URI маршрута.
     * @param mixed  $callback  Функция обратного вызова или строка вида 'Controller@method'.
     */
    public function patch(string $uri): self
    {
        $this->addRoute('PATCH', $uri);

        return $this;
    }

    /**
     * Добавляет DELETE маршрут.
     *
     * @param string $uri       URI маршрута.
     * @param mixed  $callback  Функция обратного вызова или строка вида 'Controller@method'.
     */
    public function delete(string $uri): self
    {
        $this->addRoute('DELETE', $uri);

        return $this;
    }

    /**
     * Добавляет маршрут для всех HTTP-методов.
     *
     * @param string $uri       URI маршрута.
     * @param mixed  $callback  Функция обратного вызова или строка вида 'Controller@method'.
     */
    public function any(string $uri): self
    {
        $this->addRoute('GET', $uri);
        $this->addRoute('POST', $uri);
        $this->addRoute('PUT', $uri);
        $this->addRoute('PATCH', $uri);
        $this->addRoute('DELETE', $uri);

        return $this;
    }

    public function controller($controller) {
        $this->routes[$this->current_method][$this->current_uri][] = $controller;
        return $this;
    }

    /**
     * Добавляет маршрут в массив маршрутов.
     *
     * @param string $method    HTTP-метод маршрута.
     * @param string $uri       URI маршрута.
     * @param mixed  $callback  Функция обратного вызова или строка вида 'Controller@method'.
     */
    private function addRoute(string $method, string $uri, array $cbs=[]): void
    {
        $this->routes[$method][$this->prefix.$uri] = $cbs;

        $this->current_method = $method;
        $this->current_uri = $this->prefix.$uri;
    }


    /**
     * Запускает маршрутизацию и выполняет соответствующий код.
     */
    public function dispatch(): void
    {
        $method = HttpHandler::$request->method;
        $uri = HttpHandler::$request->route;

        // Ищем маршрут, соответствующий методу и URI
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $routeUri => $callbacks) {
                if (self::matchRoute($routeUri, $uri, $params)) {
                    $this->currentRoute = $routeUri;

                    // Проходимся по списку контроллеров
                    foreach ($callbacks as $callback) {
                        // Проверяем отработал ли контроллер и завершаем скрипт

                        // Вызываем функцию обратного вызова
                        if (is_callable($callback)) {
                            call_user_func_array($callback, $params);
                        } elseif (is_string($callback) && strpos($callback, '@') !== false) {
                            // Разбираем строку 'Controller@method'
                            list($controllerClass, $methodName) = explode('@', $callback);
                            $controllerClass = $this->controllers_path . $controllerClass;

                            if (class_exists($controllerClass)) {
                                $controller = new $controllerClass();
                                if (method_exists($controller, $methodName)) {
                                    call_user_func_array([$controller, $methodName], $params);
                                } else {
                                    self::handleError("Method {$methodName} not found in controller {$controllerClass}");
                                }
                            } else {
                                self::handleError("Controller {$controllerClass} not found");
                            }
                        } else {
                            self::handleError("Invalid callback for route {$uri}");
                        }

                        if (HttpHandler::$response->finished) {
                            HttpHandler::$response->print();
                            return;
                        }
                    }
                }
            }
        }
    }

    public static function globalDispatch(array $routers) {
        foreach ($routers as $router) {
            $router->dispatch();
        }

        if (!HttpHandler::$response->finished) {
            // Если маршрут не найден
            self::handleNotFound();
        }
    }

    /**
     * Сравнивает URI маршрута с URI запроса.  Поддерживает параметры.
     *
     * @param string $routeUri  URI маршрута (например, '/users/{id}').
     * @param string $uri      URI запроса (например, '/users/123').
     * @param array  $params    Массив для сохранения параметров (например, ['id' => '123']).
     *
     * @return bool True, если маршрут соответствует запросу.
     */
    private static function matchRoute(string $routeUri, string $uri, array|null &$params): bool
    {
        // 1. Преобразуем URI маршрута в регулярное выражение.
        $routeUri = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $routeUri); // Захватываем параметры
        $routeUri = str_replace('/', '\/', $routeUri); // Экранируем слеши
        $routeUri = '^' . $routeUri . '$'; // Добавляем якоря

        // 2. Сравниваем URI запроса с регулярным выражением.
        if (preg_match('/' . $routeUri . '/', $uri, $matches)) {
            // 3. Извлекаем параметры из совпадений.
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return true;
        }

        return false;
    }

    /**
     * Обрабатывает ошибку "Маршрут не найден".
     *
     * @param string $uri     URI запроса.
     * @param string $method  HTTP-метод запроса.
     */
    private static function handleNotFound(): void
    {
        HttpHandler::$response->setFinish(
            new ApiResponse(
                404,
                false,
                "Not Found!"
            )
        );
        HttpHandler::$response->print();
    }

    /**
     * Обрабатывает другие ошибки маршрутизации.
     *
     * @param string $message  Сообщение об ошибке.
     */
    private static function handleError(string $message): void
    {
        HttpHandler::$response->setFinish(
            new ApiResponse(
                500,
                false,
                $message
            )
        );
        HttpHandler::$response->print();
    }
}


