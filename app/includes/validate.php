<?php
namespace App\Includes;
use App\Models\Templates\Register;
use App\Models\Templates\Login;
use App\Models\Templates\Project;
use App\Core\Session;

class Validate{
  public static function validateImage($global){
    if(Validate::hasInvalidUpload($_FILES[$global]['tmp_name']) !== false){
      return false;
    }

    $fileSize = $_FILES[$global]['size'];
    $fileErr = $_FILES[$global]['error'];
    $file = $_FILES[$global];
    $fileTmpName = $_FILES[$global]['tmp_name'];
    $fileType = $_FILES[$global]['type'];

    $image_data = $_FILES[$global]['tmp_name'];

    if(Validate::hasInvalidImageExtension($fileType) !== false){
      return false;
    }

    //Check if file upload had any errors.
    if($fileErr === 0){
       //Enable max file size. 500 000 bytes
       if($fileSize < MAX_UPLOAD_SIZE){
         return $file;
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

  public static function hasEmptyProject(Project $project){
    $result;
    if($project->customCheck=="on"){
      if(empty($project->name) || empty($project->description) || empty($project->link)|| empty($project->custom)){
        $result = true;
      }else{
        $result = false;
      }
    }else{
      if(empty($project->name) || empty($project->description) || empty($project->image) || empty($project->link)){
        $result = true;
      }else{
        $result = false;
      }
    }
    return $result;
  }

  public static function hasInvalidProjectLink($link){
    $result;
    if(filter_var($link, FILTER_VALIDATE_URL) === FALSE){
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

  public static function emptyValue($input){
    $result;
    if($input == ''){
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
