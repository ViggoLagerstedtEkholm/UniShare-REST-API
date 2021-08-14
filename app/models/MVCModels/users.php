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
    if($this->getConnection()->connect_error){
      die('Connection Failed: ' . $this->getConnection()->connect_error);
    }else{
      $changedate = "";
        $sql = "SELECT COUNT(*) FROM users";
        $result = $this->getConnection()->query($sql);
        $count = $result->fetch_assoc()["COUNT(*)"];
        return $count;
    }
    return 0;
  }

 function getUserCountSearch($search){
   $sql = "SELECT Count(*) FROM users WHERE userDisplayName LIKE ?";
   $stmt = mysqli_stmt_init($this->getConnection());
   mysqli_stmt_prepare($stmt, $sql);
   $search = '%' . $search . '%';
   mysqli_stmt_bind_param($stmt, "s", $search);
   mysqli_stmt_execute($stmt);
   $result = mysqli_stmt_get_result($stmt);
   $count = $result->fetch_assoc()["Count(*)"];
   return $count;
 }

 function fetchPeopleSearch($from, $to, $option, $filterOrder, $search = null){
    $queryBuilder = [
      "select" => "SELECT usersID, userFirstName, userLastName, userEmail, userDisplayName, userImage, visits, lastOnline FROM users ",
      "condition" => "WHERE userDisplayName LIKE ? ",
      "ordering" => "ORDER BY $option $filterOrder ",
      "LIMIT" => "LIMIT ?, ?;"
    ];

    if(is_null($search)){
      $sql = $queryBuilder["select"] . $queryBuilder["ordering"] . $queryBuilder["LIMIT"];
    }else{
      $sql = $queryBuilder["select"] . $queryBuilder["condition"] . $queryBuilder["ordering"] . $queryBuilder["LIMIT"];
    }

    $stmt = mysqli_stmt_init($this->getConnection());
    mysqli_stmt_prepare($stmt, $sql);

    if(is_null($search)){
      mysqli_stmt_bind_param($stmt, "ss", $from, $to);
    }else{
      $search = '%' . $search . '%';
      mysqli_stmt_bind_param($stmt, "sss", $search, $from, $to);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $users = array();
    while( $row = $result->fetch_array() )
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

    mysqli_stmt_close($stmt);

    return $users;
  }

 function userExists($attribute, $value){
    $sql = "SELECT * FROM users WHERE $attribute = ?;";
    $stmt = mysqli_stmt_init($this->getConnection());

    mysqli_stmt_prepare($stmt, $sql);
    mysqli_stmt_bind_param($stmt, "s", $value);
    mysqli_stmt_execute($stmt);
    $resultData = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($resultData);
    mysqli_stmt_close($stmt);

    if(is_null($row)){
      return null;
    }else{
      return $row;
    }
  }

  function updateUser($attribute, $value, $ID){
    $sql = "UPDATE users SET $attribute = ? WHERE usersID = ?;";
    $stmt = mysqli_stmt_init($this->getConnection());
    mysqli_stmt_prepare($stmt, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $value, $ID);
    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);
  }

 function getUser($ID){
    $sql = "SELECT * FROM users WHERE usersID = ?;";

    $stmt = mysqli_stmt_init($this->getConnection());
    mysqli_stmt_prepare($stmt, $sql);
    mysqli_stmt_bind_param($stmt, "s",$ID);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if($row = mysqli_fetch_assoc($resultData)){
      mysqli_stmt_close($stmt);
      return $row;
    }else{
      mysqli_stmt_close($stmt);
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
    $stmt = mysqli_stmt_init($this->getConnection());
    mysqli_stmt_prepare($stmt, $sql);

    $first_name = $register->first_name;
    $last_name = $register->last_name;
    $email = $register->email;
    $display_name = $register->display_name;
    $password = $register->password;

    $hashPassword = password_hash($password, PASSWORD_DEFAULT);

    mysqli_stmt_bind_param($stmt, "sssss", $first_name, $last_name, $email, $display_name, $hashPassword);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
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
   $stmt = $this->getConnection()->prepare($sql);
   $ID = Session::get(SESSION_USERID);
   $stmt->bind_param("i", $ID);
   $stmt->execute();
   $result = $stmt->get_result();
   var_dump($result);
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
