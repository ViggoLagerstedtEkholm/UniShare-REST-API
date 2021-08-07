<?php
namespace App\Models\MVCModels;

require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/UniShare/vendor/autoload.php");

use App\Models\MVCModels\Database;
use App\Models;

class Users extends Database{
  protected function getUserCount(){
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

  protected function getShowcaseUsersPage($from, $to, $option, $filterOrder){
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

    if(!mysqli_stmt_prepare($stmt, $sql)){
      echo $sql;
      //header("location: ../index.php?error=failedpaginationquery");
      //exit();
    }
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

  protected function usernameExists($display_name, $ID){
    $sql = "SELECT * FROM users WHERE userEmail = ? OR usersID = ?;";

    $stmt = mysqli_stmt_init($this->getConnection());

    if(!mysqli_stmt_prepare($stmt, $sql)){
      header("location: ../register.php?error=failedstmt");
      exit();
    }

    mysqli_stmt_bind_param($stmt, "ss", $display_name, $ID);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if($row = mysqli_fetch_assoc($resultData)){
      mysqli_stmt_close($stmt);
      return $row;
    }else{
      mysqli_stmt_close($stmt);
      $result = false;
      return $result;
    }
  }

  protected function getUser($ID){
    $sql = "SELECT * FROM users WHERE usersID = ?;";

    $stmt = mysqli_stmt_init($this->getConnection());

    if(!mysqli_stmt_prepare($stmt, $sql)){
      header("location: ../views/startpage.php?error=fetchusererror");
      exit();
    }

    mysqli_stmt_bind_param($stmt, "s",$ID);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if($row = mysqli_fetch_assoc($resultData)){
      mysqli_stmt_close($stmt);
      return $row;
    }else{
      mysqli_stmt_close($stmt);
      $result = false;
      return $result;
    }
  }

  protected function register($user){
    $sql = "INSERT INTO users (userFirstName, userLastName, userEmail, userDisplayName, usersPassword) values(?,?,?,?,?);";
    $stmt = mysqli_stmt_init($this->getConnection());

    if(!mysqli_stmt_prepare($stmt, $sql)){
      header("location: ../../register.php?error=failedstmt");
      exit();
    }

    $first_name = $user->getFirst_name();
    $last_name = $user->getLast_name();
    $email = $user->getEmail();
    $display_name = $user->getDisplay_name();
    $password = $user->getPassword();

    $hashPassword = password_hash($password, PASSWORD_DEFAULT);

    mysqli_stmt_bind_param($stmt, "sssss", $first_name, $last_name, $email, $display_name, $hashPassword);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("location: ../../views/register.php?error=none");
    exit();
  }

  protected function login($user){
    $usernameExists = $this->usernameExists($user->getEmail(), $user->getEmail());

    if($usernameExists === false){
      header("location: ../../views/login.php?error=wrongemailorpassword");
      exit();
    }

    $passwordHash = $usernameExists["usersPassword"];
    $comparePassword = password_verify($user->getPassword(), $passwordHash);

    if($comparePassword === false){
      header("location: ../../views/login.php?error=wrongemailorpassword");
      exit();
    }else if($comparePassword === true){
      session_start();
      $_SESSION["userID"] =  $usernameExists["usersID"];
      $_SESSION["userEmail"] =  $usernameExists["userEmail"];
      header("location: ../../views/startpage.php");
      exit();
    }
  }
}
