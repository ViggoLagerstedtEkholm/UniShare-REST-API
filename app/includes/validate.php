<?php
namespace App\Includes;
use App\Models\Templates\Register;
use App\Models\Templates\Login;
use App\Models\Templates\Project;
use App\Models\Templates\Degree;
use App\Core\Session;

/**
 * Validation helper class for input validation.
 * @author Viggo Lagestedt Ekholm
 */
class Validate{
  /**
   * This method checks if a file has a valid extension and file size.
   * @return bool
   */
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

  /**
   * This method checks if a file has been uploaded by the user.
   * @param file
   * @return bool
   */
  public static function hasInvalidUpload($file){
    if(!(file_exists($file)) || !(is_uploaded_file($file))) {
        return true;
    }
    return false;
  }

  /**
   * This method checks if a valid link has been uploaded by the user.
   * @param link
   * @return bool
   */
  public static function hasInvalidProjectLink($link){
    $result;
    if(filter_var($link, FILTER_VALIDATE_URL) === FALSE){
      $result = true;
    }else{
      $result = false;
    }
    return $result;
  }

  /**
   * This method checks if a valid image extension has been uploaded by the user.
   * @param fileType
   * @return bool
   */
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

  /**
   * This method checks if 2 valid dates has been uploaded by the user.
   * @param start_date
   * @param end_date
   * @return bool
   */
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

  /**
   * This method checks if a valid username has been uploaded by the user.
   * @param username
   * @return bool
   */
  public static function invalidUsername($username){
    $result;
    if(!preg_match("/^[a-zA-Z0-9]*$/", $username)){
      $result = true;
    }else{
      $result = false;
    }
    return $result;
  }

  /**
   * This method checks if a valid email has been uploaded by the user.
   * @param email
   * @return bool
   */
  public static function invalidEmail($email){
    $result;
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
      $result = true;
    }else{
      $result = false;
    }
    return $result;
  }

  /**
   * This method checks if a array has empty key => value pair.
   * @param email
   * @return bool
   */
  public static function arrayHasEmptyValue($array){
    foreach ($array as $i => $v) {
      if ($v == "" || is_null($v)){
        return true;
      }
    }
    return false;
  }

  /**
   * This method checks if 2 strings mathch.
   * @param email
   * @return bool
   */
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
