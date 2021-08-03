<?php
if(isset($_POST["submit_register"])){
  $first_name = $_POST["first_name"];
  $last_name = $_POST["last_name"];
  $email = $_POST["email"];
  $display_name = $_POST["username"];
  $password = $_POST["password"];
  $password_repeat = $_POST["password_repeat"];

  require_once 'user-inc.php';
  require_once 'database-inc.php';
  require_once 'helper_functions-inc.php';

  $user = new User($first_name, $last_name, $email, $display_name);
  $user->setPassword($password);
  $user->setPassword_repeat($password_repeat);

  $database = new Database('localhost', 'root', '', '9.0');

  if(hasEmptyInputsRegister($user) !== false){
    redirect("?error=emptyinput");
  }
  if(invalidUsername($user->getDisplay_name()) !== false){
    redirect("?error=invalidUsername");
  }
  if(invalidEmail($user->getEmail()) !== false){
    redirect("?error=invalidEmail");
  }
  if(passwordMatch($user->getPassword(), $user->getPassword_repeat()) !== false){
    redirect("?error=invalidpasswordrepeat");
  }
  if(usernameExists($database->getConn(), $user->getDisplay_name(), $user->getEmail()) !== false){
    redirect("?error=usernameoremailtaken");
  }

  createUser($database->getConn(), $user);
  $database->close();
}else{
  redirect("?error=invalidsubmit");
}

function redirect($error){
  header("location: ../register.php".$error);
  exit();
}
