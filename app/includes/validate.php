<?php
namespace App\Includes;
use App\Models\Register;
use App\Models\Login;
use App\Core\Session;

class Validate{
  public static function validateImage($global, $MAX_UPLOAD_SIZE){
    if(Validate::hasInvalidUpload($_FILES[$global]['tmp_name']) !== false){
      $ID = Session::get('userID');
      header("location: ../../profile?ID=$ID&error=invalidupload");
    }

    $fileSize = $_FILES[$global]['size'];
    $fileErr = $_FILES[$global]['error'];
    $file = $_FILES[$global];
    $fileTmpName = $_FILES[$global]['tmp_name'];
    $fileType = $_FILES[$global]['type'];

    $image_data = file_get_contents($_FILES[$global]['tmp_name']);

    if(Validate::hasInvalidImageExtension($fileType) !== false){
       header("location: ../../profile?ID=$ID&error=illegaltype");
       exit();
    }

    //Check if file upload had any errors.
    if($fileErr === 0){
       //Enable max file size. 500 000 bytes
       if($fileSize < $MAX_UPLOAD_SIZE){
         return $image_data;
       }else{
         return false;
       }
     }else{
       return false;
     }
  }

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

  public static function hasEmptyInputsRegister(Register $register){
    $result;
    if(empty($register->first_name) || empty($register->last_name)
    || empty($register->email) || empty($register->password
    || empty($register->password_repeat) || empty($register->display_name))){
      $result = true;
    }else{
      $result = false;
    }
    return $result;
  }

  public static function hasEmptyInputsLogin(Login $login){
    $result;
    if(empty($login->email) || empty($login->password))
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

  public static function match($password, $password_repeat){
    $result;
    if($password !== $password_repeat){
      $result = true;
    }else{
      $result = false;
    }
    return $result;
  }
}
