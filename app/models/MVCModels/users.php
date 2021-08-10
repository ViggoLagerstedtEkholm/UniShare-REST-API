<?php
namespace App\Models\MVCModels;
use App\Models\Register;
use App\Models\Login;
use App\Core\Session;
use App\Models\MVCModels\Database;
use App\Models;

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

        $user = new Models\User($first_name, $last_name, $email, $display_name);
        $user->setImage($image);
        $user->setID($ID);
        $user->setLastOnline($last_online);
        $user->setVistiors($visitors);
        $users[] = $user;
    }

    mysqli_stmt_close($stmt);

    return $users;
  }

 function userExists($email){
    $sql = "SELECT * FROM users WHERE userEmail = ?;";

    $stmt = mysqli_stmt_init($this->getConnection());

    mysqli_stmt_prepare($stmt, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
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

 function login(Login $login){
    $user = $this->userExists($login->email);

    if($user === false){
      return false;
    }

    $passwordHash = $user["usersPassword"];
    $comparePassword = password_verify($login->password, $passwordHash);

    if($comparePassword === false){
      return false;
    }else if($comparePassword === true){
      Session::set('userID', $user["usersID"]);
      Session::set('userEmail', $user["userEmail"]);
      return true;
    }
  }
}
