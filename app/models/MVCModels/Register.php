<?php
namespace App\Models\MVCModels;
use App\Models\Templates\Degree;
use App\Models\Templates\Course;
use App\Includes\Validate;
use App\Core\Session;
use App\Models\MVCModels\Users;

class Register extends Database implements IValidate{
  public function validate($params){
    $errors = array();
    $users = new Users();

    if(Validate::arrayHasEmptyValue($params) === true){
      $errors[] = INVALID_CREDENTIALS;
    }
    if(Validate::invalidUsername($params["display_name"]) === true){
      $errors[] = INVALID_USERNAME;
    }
    if(Validate::invalidEmail($params["email"]) === true){
      $errors[] = INVALID_MAIL;
    }
    if(Validate::match($params["password"], $params["password_repeat"]) === true){
      $errors[] = INVALID_PASSWORD_MATCH;
    }
    if(!is_null($users->userExists("userEmail", $params["email"]))){
      $errors[] = EMAIL_TAKEN;
    }
    if(!is_null($users->userExists("userDisplayName", $params["display_name"]))){
      $errors[] = USERNAME_TAKEN;
    }

    return $errors;
  }
  
  function register($params){
     $sql = "INSERT INTO users (userFirstName, userLastName, userEmail, userDisplayName, usersPassword, joined) values(?,?,?,?,?,?);";
 
     $password = $params["password"];
     $hashPassword = password_hash($password, PASSWORD_DEFAULT);
     date_default_timezone_set("Europe/Stockholm");
     $date = date('Y-m-d H:i:s');
 
     $this->insertOrUpdate($sql, 'ssssss', array($params["first_name"], $params["last_name"], $params["email"], $params["display_name"], $hashPassword, $date));
   }
}
