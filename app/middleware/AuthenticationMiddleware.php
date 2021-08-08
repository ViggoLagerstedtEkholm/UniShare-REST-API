<?php
namespace App\Middleware;
use App\Core\Session;
use App\Core\Exceptions\ForbiddenException;
use App\Core\Application;

class AuthenticationMiddleware extends Middleware{
  public array $actions = [];

  public function __construct(array $actions = []){
    $this->actions = $actions;
  }

  public function performCheck(){
    if(!Session::isLoggedIn()){
      $controller = Application::$app->getController();
      if(empty($this->actions) || in_array($controller->action, $this->actions)){
        throw new ForbiddenException();
      }
    }
  }
}
