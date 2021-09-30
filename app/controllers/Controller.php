<?php

namespace App\controllers;
use App\Middleware\Middleware;

/**
 * Abstract class handling displaying/adding middleware/json responses/calculating
 * filtering offsets for pagination.
 * @abstract
 * @author Viggo Lagestedt Ekholm
 */
abstract class Controller
{
    protected array $middlewares = [];

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
}
