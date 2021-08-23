<?php
namespace App\Models\MVCModels;
use App\Models\MVCModels\Database;
use App\Models\MVCModels\UserSession;
use App\Core\Session;
use App\Core\Cookie;

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
   
   return $this->fetchResults($result);
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

  function terminateAccount($userID){
    $sql = "DELETE FROM users WHERE usersID = ?;";
    $this->executeQuery($sql, 'i', array($userID));
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

  function uploadImage($image, $ID)
  {
    $sql = "UPDATE users SET userImage =? WHERE usersID = ?;";
    $this->insertOrUpdate($sql, 'si', array($image, $ID));
  }

  function addVisitor($ID, $user){
    $visits = $user["visits"];
    $updatedVisits = $visits + 1;
    $sql = "UPDATE users SET visits =? WHERE usersID = ?;";
    $this->insertOrUpdate($sql, 'ii', array($updatedVisits , $ID));
    return $updatedVisits;
  }

  function addVisitDate($ID){
    $sql = "UPDATE users SET lastOnline = ? WHERE usersID = ?;";

    date_default_timezone_set("Europe/Stockholm");
    $date = date('Y-m-d H:i:s');

    $this->insertOrUpdate($sql, 'si', array($date, $ID));
    return $date;
  }
}
