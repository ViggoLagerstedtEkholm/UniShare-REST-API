<?php
namespace App\Includes\Authenticate;

require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/UniShare/vendor/autoload.php");

use \App\Controllers;
use \App\Includes;
use \App\Models;

if(isset($_POST["submit_login"])){
  $email = $_POST["email"];
  $password = $_POST["password"];

  $user = new Models\User("", "", $email, "");
  $user->setPassword($password);

  $usersController = new Controllers\UsersController();
  $usersController->connect_database();

  if(Includes\Validate::hasEmptyInputsLogin($user) !== false){
    redirect("?error=emptyinput");
    exit();
  }

  $usersController->login_user($user);
}else{
  header("location: ./views/login.php");
  exit();
}

function redirect($error){
  header("location: ./views/login.php".$error);
  exit();
}
