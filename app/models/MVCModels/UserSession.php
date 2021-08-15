<?php
namespace App\Models\MVCModels;
use App\Models\MVCModels\Database;
use App\Core\Cookie;
use App\Core\Session;

class userSession extends Database{
  public function deleteExistingSession($ID, $user_agent){
    $sql = "DELETE FROM sessions WHERE userID = ? AND userAgent = ?";
    $this->delete($sql, 'ii', array($ID, $user_agent));
  }

  public function insertSession($ID, $user_agent, $hash){
    $insertSQL = "INSERT INTO sessions (userID, userAgent, session) values(?, ?, ?);";
    $this->insertOrUpdate($sql, 'sss', array($ID, $user_agent, $hash));
  }

  public function getSessionFromCookie(){
    if(Cookie::exists(REMEMBER_ME_COOKIE_NAME)){
      $SQL = "SELECT * FROM sessions WHERE userAgent = ? AND session = ? LIMIT 1";

      $user_agent = Session::uagent_no_version();
      $session = Cookie::get(REMEMBER_ME_COOKIE_NAME);

      $result = $this->executeQuery($sql, 'ss', array($user_agent, $session));
      return $result->fetch_assoc();
    }
  }
}
