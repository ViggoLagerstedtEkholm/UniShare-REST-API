<?php

namespace App\controllers;

use App\Middleware\Middleware;
use App\Core\Application;
use App\Core\Response;
use JetBrains\PhpStorm\Pure;

/**
 * Abstract class handling displaying/adding middleware/json responses/calculating
 * filtering offsets for pagination.
 * @abstract
 * @author Viggo Lagestedt Ekholm
 */
abstract class Controller
{
    public string $action = '';
    protected array $middlewares = [];
    protected Response $response;

    /**
     * Sets the middleware for the controller.
     * @param Middleware $middleware
     */
    public function setMiddlewares(Middleware $middleware)
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * Gets the middleware for the controller.
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    protected function setStatusCode($code): bool|int
    {
        return $this->response->setStatusCode($code);
    }

    protected function setResponse($response): bool|string
    {
        return $this->response->setResponseBody($response, JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    /**
     * Returns a json response that we can use to handle responses from user requests.
     * @param mixed $resp
     * @param int $code
     * @param int $option
     * @return bool|string|null
     */
    protected function jsonResponse(mixed $resp, int $code, int $option = JSON_PARTIAL_OUTPUT_ON_ERROR): bool|string|null
    {
        $response = new Response();
        $response->setStatusCode($code);

        if(!is_null($resp)){
            return $response->setResponseBody($resp, $option);
        }else{
            return null;
        }
    }
}
