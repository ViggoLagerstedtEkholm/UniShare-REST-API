<?php
namespace App\Controllers;
use App\Core\Application;
use App\Core\Request;
use App\Models\Register;
use App\Models\Login;
use App\Includes\Validate;
use App\Models\MVCModels\Users;
use App\Core\Session;

class AuthenticationController{
  public function view_login(){
    return Application::$app->router->renderView('login', []);
  }

  public function view_register(){
    return Application::$app->router->renderView('register', []);
  }

  public function logout(){
    Session::deleteAll();
    header("location: ./");
  }

  public function login(Request $request)
  {
    if($request->isPost()){
      $login = new Login();
      $users = new Users();

      $login->populateAttributes($request->getBody());

      var_dump($login);

      if(Validate::hasEmptyInputsLogin($login) !== false){
        header("location: ./login?error=emptyfield");
        exit();
      }

      $users->login($login);
    }

    return Application::$app->router->renderView('login', $params);
  }

  public function register(Request $request)
  {
    if($request->isPost()){
      $register = new Register();
      $users = new Users();

      $register->populateAttributes($request->getBody());

      if(Validate::hasEmptyInputsRegister($register) !== false){
        header("location: ./register?error=emptyfield");
        exit();
      }
      if(Validate::invalidUsername($register->display_name) !== false){
        header("location: ./register?error=invalidUsername");
        exit();
      }
      if(Validate::invalidEmail($register->email) !== false){
        header("location: ./register?error=invalidEmail");
        exit();
      }
      if(Validate::match($register->password, $register->password_repeat) !== false){
        header("location: ./register?error=invalidPasswordMatch");
        exit();
      }
      if(!is_null($users->userExists($register->email))){
        header("location: ./register?error=emailtaken");
        exit();
      }
      $users->register($register);
    }
  }
}

?>
