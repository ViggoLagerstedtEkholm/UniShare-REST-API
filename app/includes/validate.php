<?php
namespace App\Includes;
use App\Models\Templates\Register;
use App\Models\Templates\Login;
use App\Models\Templates\Project;
use App\Models\Templates\Degree;
use App\Core\Session;

class Validate{
  public static function hasValidUpload($global){
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
         return true;
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

  public static function hasInvalidDates($start_date, $end_date){
    $start_date_converted = strtotime($start_date);
    $end_date_converted = strtotime($end_date);

    $result;
    if($start_date_converted > $end_date_converted)
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

  public static function arrayHasEmptyValue($array){
    foreach ($array as $i => $v) {
      if (empty($v) || is_null($v)){
        return true;
      }
    }
    return false;
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
