<?php
namespace App\Includes;

class Validate{
  public static function hasInvalidUpload($file){
    if(!(file_exists($file)) || !(is_uploaded_file($file))) {
        return true;
    }
    return false;
  }

  public static function hasEmptyProject($project){
    $result;
    if(empty($project->getLink()) || empty($project->getName()) || empty($project->getImage()) || empty($project->getDescription())){
      $result = true;
    }else{
      $result = false;
    }
    return $result;
  }

  public static function hasInvalidImageExtension($fileType){
    $allowed = array("image/jpeg", "image/gif", "image/png");
    $result;
    if(!in_array($fileType, $allowed)) {
      $result = true;
    }else{
      $result = false;
    }
    return $result;
  }

  public static function hasEmptyInputsRegister($user){
    $result;
    if(empty($user->getFirst_name()) || empty($user->getLast_name())
    || empty($user->getEmail()) || empty($user->getPassword()
    || empty($user->getPassword_repeat()) || empty($user->getDisplay_name()))){
      $result = true;
    }else{
      $result = false;
    }
    return $result;
  }

  public static function hasEmptyInputsLogin($user){
    $result;
    if(empty($user->getEmail()) || empty($user->getPassword()))
    {
      $result = true;
    }else{
      $result = false;
    }
    return $result;
  }

  public static function invalidUsername($username){
    $result;
    if(!preg_match("/^[a-zA-Z0-9]*$/", $username)){
      $result = true;
    }else{
      $result = false;
    }
    return $result;
  }

  public static function invalidEmail($email){
    $result;
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
      $result = true;
    }else{
      $result = false;
    }
    return $result;
  }

  public static function passwordMatch($password, $password_repeat){
    $result;
    if($password !== $password_repeat){
      $result = true;
    }else{
      $result = false;
    }
    return $result;
  }
}
