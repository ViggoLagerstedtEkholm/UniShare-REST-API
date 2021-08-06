<?php
namespace App\Includes\Authenticate;

require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/UniShare/vendor/autoload.php");

use \App\Controllers;
use \App\Includes;
use \App\Models;

if(isset($_POST["submit_register"])){
  $first_name = $_POST["first_name"];
  $last_name = $_POST["last_name"];
  $email = $_POST["email"];
  $display_name = $_POST["username"];
  $password = $_POST["password"];
  $password_repeat = $_POST["password_repeat"];

  $user = new Models\User($first_name, $last_name, $email, $display_name);
  $user->setPassword($password);
  $user->setPassword_repeat($password_repeat);

  $usersController = new Controllers\UsersController();
  $usersController->connect_database();

  if(Includes\Validate::hasEmptyInputsRegister($user) !== false){
    redirect("?error=emptyinput");
  }
  if(Includes\Validate::invalidUsername($user->getDisplay_name()) !== false){
    redirect("?error=invalidUsername");
  }
  if(Includes\Validate::invalidEmail($user->getEmail()) !== false){
    redirect("?error=invalidEmail");
  }
  if(Includes\Validate::passwordMatch($user->getPassword(), $user->getPassword_repeat()) !== false){
    redirect("?error=invalidpasswordrepeat");
  }
  if($usersController->username_exists($user->getDisplay_name(), $user->getEmail()) !== false){
    redirect("?error=usernameoremailtaken");
  }

  $usersController->register_user($user);
  $usersController->close_database();
}else{
  redirect("?error=invalidsubmit");
}

function redirect($error){
  header("location: ./views/register.php".$error);
  exit();
}
?>
