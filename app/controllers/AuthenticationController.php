<?php
namespace App\Controllers;
use App\Core\Application;
use App\Core\Request;
use App\Models\Templates\Register;
use App\Models\Templates\Login;
use App\Models\MVCModels\Users;
use App\Middleware\AuthenticationMiddleware;
use App\Includes\Validate;

class AuthenticationController extends Controller{

  public function __construct(){
    $this->setMiddlewares(new AuthenticationMiddleware(['logout']));
    $this->users = new Users();
  }

  public function loginWithCookie(){
    $this->users->loginFromCOOKIE();
  }

  public function view_login(){
    return $this->display('login', 'login', []);
  }

  public function view_register(){
    return $this->display('register', 'register', []);
  }

  public function logout(){
    $this->users->logout();
    Application::$app->redirect("./");
  }

  public function login(Request $request)
  {
    $login = new Login();
    $login->populateAttributes($request->getBody());

    if(Validate::hasEmptyInputsLogin($login) === true){
      Application::$app->redirect("./login?error=" . INVALID_CREDENTIALS);
      exit();
    }

    $success = $this->users->login($login);

    if($success){
      Application::$app->redirect("./");
    }else{
      Application::$app->redirect("./login?error=" . INVALID_CREDENTIALS);
    }

    return $this->display('login', $params);
  }

  public function register(Request $request)
  {
    $fields = ["userEmail", "userDisplayName"];

    $register = new Register();

    $register->populateAttributes($request->getBody());

    $error = array();

    if(Validate::hasEmptyInputsRegister($register) === true){
      $error [] = EMPTY_FIELDS;
    }
    if(Validate::invalidUsername($register->display_name) === true){
      $error [] = INVALID_USERNAME;
    }
    if(Validate::invalidEmail($register->email) === true){
      $error [] = INVALID_MAIL;
    }
    if(Validate::match($register->password, $register->password_repeat) === true){
      $error [] = INVALID_PASSWORD_MATCH;
    }
    if(!is_null($this->users->userExists($fields[0], $register->email))){
      $error [] = EMAIL_TAKEN;
    }
    if(!is_null($this->users->userExists($fields[1], $register->display_name))){
      $error [] = INVALID_USERNAME;
    }

    $URL;
    $errorCount = count($error);
    if($errorCount > 0){
      for ($x = 0; $x < $errorCount; $x++) {
        $x == $errorCount - 1 ? $URL .= $error[$x] : $URL .= $error[$x] . "&error=";
      }
      Application::$app->redirect("./register?error=" . $URL);
      exit();
    }

    $this->users->register($register);
    Application::$app->redirect("./");
  }
}
