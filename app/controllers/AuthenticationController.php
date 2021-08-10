<?php
namespace App\Controllers;
use App\Core\Application;
use App\Core\Request;
use App\Models\Register;
use App\Models\Login;
use App\Includes\Validate;
use App\Models\MVCModels\Users;
use App\Core\Session;
use App\Middleware\AuthenticationMiddleware;
use App\Core\ImageHandler;

class AuthenticationController extends Controller{

  public function __construct(){
    $this->setMiddlewares(new AuthenticationMiddleware(['logout']));
    $this->users = new Users();
  }

  public function view_login(){
    return Application::$app->router->renderView('login', []);
  }

  public function view_register(){
    return Application::$app->router->renderView('register', []);
  }

  public function logout(){
    Session::deleteAll();
    Application::$app->redirect("./");
  }

  public function login(Request $request)
  {
    if($request->isPost()){
      $login = new Login();

      $login->populateAttributes($request->getBody());

      if(Validate::hasEmptyInputsLogin($login) !== false){
        Application::$app->redirect("./login?error=" . INVALID_CREDENTIALS);
        exit();
      }

      $success = $this->users->login($login);
      if($success){
        Application::$app->redirect("./");
      }else{
        Application::$app->redirect("./login?error=" . INVALID_CREDENTIALS);
      }
    }

    return Application::$app->router->renderView('login', $params);
  }

  public function register(Request $request)
  {
    if($request->isPost()){
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
      if(!is_null($users->userExists($register->email))){
        $error [] = EMAIL_TAKEN;
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
    }
  }
}
