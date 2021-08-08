<?php
namespace App\Controllers;
use App\Middleware\Middleware;
use App\Core\Application;

abstract class Controller{
  public string $action = '';
  protected array $middlewares = [];

  public function setMiddlewares(Middleware $middleware)
  {
      $this->middlewares[] = $middleware;
  }

  public function getMiddlewares(): array
  {
      return $this->middlewares;
  }

  public function display($page, $params){
    return Application::$app->router->renderView($page, $params);
  }
}
