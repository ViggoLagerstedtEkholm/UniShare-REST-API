<?php
namespace App\Middleware;
use App\Core\Session;
use App\Core\Exceptions\ForbiddenException;
use App\Core\Exceptions\PrivilegeException;
use App\Core\Application;
use App\Models\MVCModels\Users;

/**
 * Authentication middleware for handling controller access.
 * @author Viggo Lagestedt Ekholm
 */
class AuthenticationMiddleware extends Middleware{
  public array $actions = [];
  public bool $requiresAdminPrivilege;

  public function __construct(array $actions = [], bool $requiresAdminPrivilege = false){
    $this->actions = $actions;
    $this->requiresAdminPrivilege = $requiresAdminPrivilege;
  }

  /**
   * Check the session to see if a user exists and if required that this user has
   * the required privilege to access the Controller method. If sufficient privelege is not met
   * we either throw a privelege exception or a forbidden exception depending on the case.
   */
  public function performCheck(){
    if($this->requiresAdminPrivilege && Session::isLoggedIn()){
      $model = new Users();
      $user = $model->getUser(Session::get(SESSION_USERID));
      $privilege = $user['privilege'];

      if($privilege != ADMIN) {
        throw new PrivilegeException();
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
