<?php
namespace App\Models\MVCModels;
use App\Models\Templates\Degree;
use App\Models\Templates\Course;
use App\Includes\Validate;
use App\Core\Session;
use App\Models\MVCModels\UserSession;
use App\Models\MVCModels\Users;
use App\Core\Cookie;

class Login extends Database implements IValidate{
  public function validate($params){
    $errors = array();

    if(Validate::arrayHasEmptyValue($params) === true){
      $errors[] = INVALID_CREDENTIALS;
    }

    return $errors;
  }
  
  function loginFromCOOKIE(){
    $userSession = new UserSession();
    $users = new Users();
    
    $userSession = $userSession->getSessionFromCookie();
    $ID = $userSession['userID'];
    $session = $userSession['session'];
 
    if($session != '' && $ID != '' && !empty($userSession)){
     $user = $users->getUser($ID);
     $email = $user["userEmail"];
     $privilege = $user["privilege"];
 
     Session::set(SESSION_USERID, $ID);
     Session::set(SESSION_MAIL, $email);
     Session::set(SESSION_PRIVILEGE, $privilege);
   }
  }
 
  function login($params){
     $userSession = new UserSession();    
     $users = new Users();

     $user = $users->userExists("userEmail", $params['email']);
 
     if($user === false){
       return false;
     }
 
     $passwordHash = $user["usersPassword"];
     $comparePassword = password_verify($params['password'], $passwordHash);
 
     if($comparePassword === false){
       return false;
     }else if($comparePassword === true){
       $ID = $user["usersID"];
       $Email = $user["userEmail"];
       $privilege = $user["privilege"];
 
       Session::set(SESSION_USERID, $ID);
       Session::set(SESSION_MAIL, $Email);
       Session::set(SESSION_PRIVILEGE, $privilege);
 
       if($params['rememberMe'] == "Yes"){
           $hash = md5(uniqid(rand(), true));
           $user_agent = Session::uagent_no_version();
           Cookie::set(REMEMBER_ME_COOKIE_NAME, $hash, REMEMBER_ME_COOKIE_EXPIRY);
           $userSession->deleteExistingSession($ID, $user_agent); //If any previous session exists, remove.
           $userSession->insertSession($ID,$user_agent,$hash); //Insert the new session ID.
       }
       return true;
     }
   }
}
