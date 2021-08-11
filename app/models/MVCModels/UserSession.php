<?php
namespace App\Models\MVCModels;
use App\Models\MVCModels\Database;
use App\Core\Cookie;
use App\Core\Session;

class userSession extends Database{
  public function deleteExistingSession($ID, $user_agent){
    $deleteExistingSQL = "DELETE FROM sessions WHERE userID = ? AND userAgent = ?";
    $stmt = mysqli_stmt_init($this->getConnection());
    mysqli_stmt_prepare($stmt, $deleteExistingSQL);
    mysqli_stmt_bind_param($stmt, "ss", $ID, $user_agent);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
  }

  public function insertSession($ID, $user_agent, $hash){
    $insertSQL = "INSERT INTO sessions (userID, userAgent, session) values(?, ?, ?);";
    $stmt = mysqli_stmt_init($this->getConnection());
    mysqli_stmt_prepare($stmt, $insertSQL);
    mysqli_stmt_bind_param($stmt, "sss", $ID, $user_agent, $hash);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
  }

  public function getSessionFromCookie(){
    if(Cookie::exists(REMEMBER_ME_COOKIE_NAME)){
      $SQL = "SELECT * FROM sessions WHERE userAgent = ? AND session = ? LIMIT 1";
      $stmt = mysqli_stmt_init($this->getConnection());
      mysqli_stmt_prepare($stmt, $SQL);

      $user_agent = Session::uagent_no_version();
      $session = Cookie::get(REMEMBER_ME_COOKIE_NAME);

      mysqli_stmt_bind_param($stmt, "ss", $user_agent, $session);
      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);
      mysqli_stmt_close($stmt);
      return $result->fetch_assoc();
    }
  }
}
