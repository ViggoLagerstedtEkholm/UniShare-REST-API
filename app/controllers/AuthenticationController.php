<?php
namespace App\Controllers;
use App\Core\Application;
use App\Core\Request;
use App\Models\MVCModels\Users;
use App\Models\MVCModels\Register;
use App\Models\MVCModels\Login;
use App\Middleware\AuthenticationMiddleware;
use App\Includes\Validate;
use App\Core\Session;

class AuthenticationController extends Controller{
  private $users;
  private $login;
  private $register;

  public function __construct(){
    $this->setMiddlewares(new AuthenticationMiddleware(['logout']));
    $this->users = new Users();
    $this->login = new Login();
    $this->register = new Register();
  }

  public function loginWithCookie(){
    $this->login->loginFromCOOKIE();
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
    $body = $request->getBody();

    $params = [
      'email' => $body["email"],
      'password' => $body["password"],
      'rememberMe' => $body['rememberMe']
    ];

    $errors = $this->login->validate($params);

    if(count($errors) > 0){
      $errorList = http_build_query(array('error' => $errors));
      Application::$app->redirect("./login?$errorList");
      exit();
    }

    $success = $this->login->login($params);

    if($success){
      $userID = Session::get(SESSION_USERID);
      Application::$app->redirect("./profile?ID=$userID");
    }else{
      Application::$app->redirect("./login?error=" . INVALID_CREDENTIALS);
    }
  }

  public function register(Request $request)
  {
    $body = $request->getBody();

    $params = [
      'first_name' => $body["first_name"],
      'last_name' => $body["last_name"],
      'email' => $body['email'],
      'display_name' => $body['display_name'],
      'password' => $body['password'],
      'password_repeat' => $body['password_repeat'],
    ];

    $errors = $this->register->validate($params);

    if(count($errors) > 0){
      $errorList = http_build_query(array('error' => $errors));
      Application::$app->redirect("./register?$errorList");
      exit();
    }

    $this->register->register($params);

    Application::$app->redirect("./login");
  }
}
