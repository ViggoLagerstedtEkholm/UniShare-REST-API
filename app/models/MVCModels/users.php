<?php
namespace App\Models\MVCModels;
use App\Models\Templates\Register;
use App\Models\Templates\Login;
use App\Models\Templates\User;
use App\Core\Session;
use App\Core\Cookie;
use App\Models\MVCModels\Database;
use App\Models\MVCModels\UserSession;

class Users extends Database{
 function getUserCount(){
   $sql = "SELECT Count(*) FROM users";
   $result = $this->executeQuery($sql);
   return $result->fetch_assoc()["Count(*)"];
  }

 function getUserCountSearch($search){
   $MATCH = $this->builtMatchQuery('users', $search, 'usersID');
   $sql = "SELECT Count(*) FROM users WHERE $MATCH";
   $result = $this->executeQuery($sql);
   return $result->fetch_assoc()["Count(*)"];
 }

 function fetchPeopleSearch($from, $to, $option, $filterOrder, $search = null){
   $option ?? $option = "userDisplayName";
   $filterOrder ?? $filterOrder = "DESC";
   if(!is_null($search)){
      $MATCH = $this->builtMatchQuery('users', $search, 'usersID');

      $searchQuery = "SELECT *
                      FROM users
                      WHERE $MATCH
                      ORDER BY $option $filterOrder
                      LIMIT ?, ?;";

     $result = $this->executeQuery($searchQuery, 'ii', array($from, $to));
   }else{
     $searchQuery = "SELECT *
                     FROM users
                     ORDER BY $option $filterOrder
                     LIMIT ?, ?;";

     $result = $this->executeQuery($searchQuery, 'ii', array($from, $to));
   }

    $users = array();
    while( $row = $result->fetch_array())
    {
        $ID = $row['usersID'];
        $first_name = $row['userFirstName'];
        $last_name = $row['userLastName'];
        $email =  $row['userEmail'];
        $display_name = $row['userDisplayName'];
        $image = base64_encode($row['userImage']);
        $last_online = $row['lastOnline'];
        $visitors = $row['visits'];

        $user = new User($first_name, $last_name, $email, $display_name);
        $user->image = $image;
        $user->ID = $ID;
        $user->last_online = $last_online;
        $user->visitors = $visitors;
        $users[] = $user;
    }
    return $users;
  }

 function userExists($attribute, $value){
    $sql = "SELECT * FROM users WHERE $attribute = ?;";
    $result = $this->executeQuery($sql, 's', array($value));
    $row = $result->fetch_assoc();
    if(is_null($row)){
      return null;
    }else{
      return $row;
    }
  }

  function updateUser($attribute, $value, $ID){
    $sql = "UPDATE users SET $attribute = ? WHERE usersID = ?;";
    $this->insertOrUpdate($sql, 'si', array($value, $ID));
  }

 function getUser($ID){
    $sql = "SELECT * FROM users WHERE usersID = ?;";

    $result = $this->executeQuery($sql, 'i', array($ID));

    if($row = $result->fetch_assoc()){
      return $row;
    }else{
      return false;
    }
  }

  function logout(){
    $userSession = new UserSession();
    $session = $userSession->getSessionFromCookie();
    $ID = Session::get(SESSION_USERID);
    $user_agent = Session::uagent_no_version();

    if(!empty($session)){
      $userSession->deleteExistingSession($ID, $user_agent);
    }

    Session::delete(SESSION_USERID);
    Session::delete(SESSION_MAIL);
    Session::delete(SESSION_PRIVILEGE);

    if(Cookie::exists(REMEMBER_ME_COOKIE_NAME)) {
      Cookie::delete(REMEMBER_ME_COOKIE_NAME);
    }
  }

 function register(Register $register){
    $sql = "INSERT INTO users (userFirstName, userLastName, userEmail, userDisplayName, usersPassword) values(?,?,?,?,?);";

    $password = $register->password;
    $hashPassword = password_hash($password, PASSWORD_DEFAULT);

    $this->insertOrUpdate($sql, 'sssss', array($register->first_name, $register->last_name, $register->email, $register->display_name, $hashPassword));
  }

 function loginFromCOOKIE(){
   $userSession = new UserSession();

   $userSession = $userSession->getSessionFromCookie();
   $ID = $userSession['userID'];
   $session = $userSession['session'];

   if($session != '' && $ID != '' && !empty($userSession)){
    $user = $this->getUser($ID);
    $email = $user["userEmail"];

    Session::set(SESSION_USERID, $ID);
    Session::set(SESSION_MAIL, $email);
  }
 }

 function userHasDegreeID($newActiveDegreeID){
   $sql = "SELECT degreeID FROM degrees
           JOIN users
           ON degrees.userID = users.usersID
           WHERE usersID = ?;";

   $ID = Session::get(SESSION_USERID);

   $result = $this->executeQuery($sql, 'i', array($ID));

   $IDs = array();
   while( $row = $result->fetch_array()){
      $IDs[] = $row["degreeID"];
   }

   $exists = in_array($newActiveDegreeID, $IDs);

   if($exists){
     return true;
   }else{
     return false;
   }
 }

 function login(Login $login){
    $userSession = new UserSession();
    $user = $this->userExists("userEmail", $login->email);

    if($user === false){
      return false;
    }

    $passwordHash = $user["usersPassword"];
    $comparePassword = password_verify($login->password, $passwordHash);

    if($comparePassword === false){
      return false;
    }else if($comparePassword === true){
      $ID = $user["usersID"];
      $Email = $user["userEmail"];
      $privilege = $user["privilege"];

      Session::set(SESSION_USERID, $ID);
      Session::set(SESSION_MAIL, $Email);
      Session::set(SESSION_PRIVILEGE, $privilege);

      if($login->rememberMe == "on"){
          echo "reached";
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
