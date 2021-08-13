<?php
namespace App\Controllers;

use App\Middleware\Middleware;

use App\Core\Application;
use App\Core\Session;
use App\Core\Request;
use App\Core\ImageHandler;

use App\Models\MVCModels\Users;
use App\Models\MVCModels\Profiles;
use App\Models\MVCModels\Projects;
use App\Models\MVCModels\Courses;

use App\Includes\Validate;

abstract class Controller{
  public string $action = '';
  protected array $middlewares = [];
  private ?ImageHandler $imageHandler;
  private ?Profiles $profiles;
  private ?Users $users;
  private ?Projects $projects;
  private ?Courses $courses;
  private ?Degrees $degrees;
  
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

  public function jsonResponse($resp){
    http_response_code(200);
    return json_encode($resp);
  }
}
