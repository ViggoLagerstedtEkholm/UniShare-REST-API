<?php

namespace App\core;

use App\core\Exceptions\NotFoundException;

/**
 * Router class to handle requests and routes.
 * @author Viggo Lagestedt Ekholm
 */
class Router
{
    public Request $request;
    public Response $response;
    protected array $routes = [];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Add route to the routes array. FOR GET
     * @param string $path
     * @param $callback
     */
    public function get(string $path, $callback)
    {
        $this->routes['get'][$path] = $callback;
    }

    /**
     * Add route to the routes array. FOR POST
     * @param $path
     * @param $callback
     */
    public function post($path, $callback)
    {
        $this->routes['post'][$path] = $callback;
    }

    /**
     * This method resolves the request and makes sure we run the middleware
     * before the user can access the requested URL. The call to call_user_func calls the
     * action method in the responsible controller.
     * @return mixed
     * @throws NotFoundException
     */
    public function resolve(): mixed
    {
        $path = $this->request->getPath();
        $method = $this->request->getMethod();
        $callback = $this->routes[$method][$path] ?? false;

        if ($callback === false) {
            return throw new NotFoundException();
        }

        if (is_array($callback)) {
            $controller = new $callback[0](); //Class
            $controller->action = $callback[1]; //Method
            $callback[0] = $controller;
            Application::$app->setController($controller);

            //Check middleware for authentication.
            foreach (Application::$app->getController()->getMiddlewares() as $middleware) {
                $middleware->performCheck();
            }
        }

        return call_user_func($callback, $this->request);
    }
}
