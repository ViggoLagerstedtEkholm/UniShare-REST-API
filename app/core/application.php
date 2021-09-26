<?php

namespace App\core;

use App\controllers\Controller;
use Exception;

/**
 * Application for handling routing/responses/redirects etc.
 * @author Viggo Lagestedt Ekholm
 */
class Application
{
    public static string $ROOT_DIR;
    public Router $router;
    public Request $request;
    public Response $response;
    public static Application $app;
    private ?Controller $controller = null;

    public function __construct($rootPath)
    {
        self::$ROOT_DIR = $rootPath;
        self::$app = $this;

        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);
    }

    /**
     * Route to the given URL. If this fails, we either get ForbiddenException (path does not exist)
     * or that the user lack privileges with PrivilegeException.
     */
    public function run()
    {
        try {
            echo $this->router->resolve();
        }
        catch (Exception $e) {
            $this->response->setStatusCode($e->getCode());
        }
    }

    /**
     * Set the controller.
     * @param Controller $controller
     */
    public function setController(Controller $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Get the controller.
     * @return Controller|null controller object.
     */
    public function getController(): ?Controller
    {
        return $this->controller;
    }
}
