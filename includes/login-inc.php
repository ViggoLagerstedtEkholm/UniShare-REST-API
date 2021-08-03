<?php
if(isset($_POST["submit_login"])){
  $email = $_POST["email"];
  $password = $_POST["password"];

  require_once 'user-inc.php';
  require_once 'database-inc.php';
  require_once 'helper_functions-inc.php';

  $user = new User("", "", $email, "");
  $user->setPassword($password);
  $database = new Database('localhost', 'root', '', '9.0');

  if(hasEmptyInputsLogin($user) !== false){
    redirect("?error=emptyinput");
    exit();
  }

  loginUser($database->getConn(), $user);
}else{
  header("location: ../login.php");
  exit();
}

function redirect($error){
  header("location: ../login.php".$error);
  exit();
}
