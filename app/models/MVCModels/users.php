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
        $result = $this->getConnection()->query($sql)->fetch_assoc();
        return $result;
    }
    return 0;
  }

 function getShowcaseUsersPage($from, $to, $option, $filterOrder){
    $sql = "";
    switch($option){
      case "none":
      $sql = "SELECT usersID, userFirstName, userLastName, userEmail, userDisplayName, userImage, visits, lastOnline FROM users LIMIT ?, ?;";
      break;
      case "visits":
        switch($filterOrder){
          case "DESC":
          $sql = "SELECT usersID, userFirstName, userLastName, userEmail, userDisplayName, userImage, visits, lastOnline FROM users ORDER BY visits DESC LIMIT ?, ?; ";
          break;
          case "ASC":
          $sql = "SELECT usersID, userFirstName, userLastName, userEmail, userDisplayName, userImage, visits, lastOnline FROM users ORDER BY visits ASC LIMIT ?, ?; ";
          break;
        }
      break;
      case "last_online":
        switch($filterOrder){
          case "DESC":
          $sql = "SELECT usersID, userFirstName, userLastName, userEmail, userDisplayName, userImage, visits, lastOnline FROM users ORDER BY lastOnline DESC LIMIT ?, ?; ";
          break;
          case "ASC":
          $sql = "SELECT usersID, userFirstName, userLastName, userEmail, userDisplayName, userImage, visits, lastOnline FROM users ORDER BY lastOnline ASC LIMIT ?, ?; ";
          break;
        }
      break;
      default:
      header("location: ../index.php?error=failedorderquery");
    }

    $stmt = mysqli_stmt_init($this->getConnection());
    mysqli_stmt_prepare($stmt, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $from, $to);
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
        $user->setImage($image);
        $user->setID($ID);
        $user->setLastOnline($last_online);
        $user->setVistiors($visitors);
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

      Session::set(SESSION_USERID, $ID);
      Session::set(SESSION_MAIL, $Email);

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
