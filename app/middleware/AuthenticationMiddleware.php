<?php
namespace App\Middleware;
use App\Core\Session;
use App\Core\Exceptions\ForbiddenException;
use App\Core\Application;
use App\Models\MVCModels\Users;

class AuthenticationMiddleware extends Middleware{
  public array $actions = [];
  public bool $requiresAdminPrivilege;

  public function __construct(array $actions = [], bool $requiresAdminPrivilege = false){
    $this->actions = $actions;
    $this->requiresAdminPrivilege = $requiresAdminPrivilege;
  }

  public function performCheck(){
    if($this->requiresAdminPrivilege && Session::isLoggedIn()){
      $model = new Users();
      $user = $model->getUser(Session::get(SESSION_USERID));
      $privilege = $user['privilege'];

      if($privilege != 10) {
        throw new ForbiddenException();
      }
    }

    if(!Session::isLoggedIn()){
      $controller = Application::$app->getController();
      if(empty($this->actions) || in_array($controller->action, $this->actions)){
          throw new ForbiddenException();
      }
    }
  }
}
