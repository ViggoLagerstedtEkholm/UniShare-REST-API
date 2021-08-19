<?php
namespace App\Controllers;
use App\Middleware\Middleware;
use App\Core\Application;
use App\Core\Session;
use App\Core\Request;
use App\Core\ImageHandler;
use App\Includes\Validate;

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

  public function display($folder, $page, $params){
    return Application::$app->router->renderView($folder, $page, $params);
  }

  public function jsonResponse($resp, $code){
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: applicaton/json; charset=UTF-8");
    http_response_code($code);
    return json_encode($resp);
  }
}
