<?php
namespace App\Models\MVCModels;
use App\Models\MVCModels\Database;
use App\Core\Session;
use App\Includes\Validate;

class Posts extends Database implements IValidate{
  public function validate($params){
    $errors = array();
    
    if(Validate::arrayHasEmptyValue($params) === true){
      $errors[] = EMPTY_FIELDS;
    }

    return $errors;
  }
  
  function getPostCount($forumID){
    $sql = "SELECT COUNT(*) FROM posts WHERE forumID = ?;";
    $result = $this->executeQuery($sql, 'i', array($forumID));
    return $result->fetch_assoc()["COUNT(*)"];
  }
  
  function addPost($userID, $forumID, $text){
    $sql = "INSERT INTO posts(userID, forumID, text, date) values(?,?,?,?);";
    date_default_timezone_set("Europe/Stockholm");
    $date = date('Y-m-d H:i:s');
    $inserted = $this->insertOrUpdate($sql, 'iiss', array($userID, $forumID, $text, $date));
    return $inserted;
  }
  
  function getForumPostInterval($from, $to, $forumID){
    $sql = "SELECT posts.*, users.userDisplayName, users.userImage 
            FROM posts 
            JOIN users 
            ON posts.userID = users.usersID
            WHERE forumID = ?
            LIMIT ?, ?;";
    $result = $this->executeQuery($sql, 'iii', array($forumID, $from, $to));
    return $this->fetchResults($result);
  }
  
  function getPosts($forumID){
    $sql = "SELECT posts.*, users.userDisplayName, users.userImage FROM posts 
            JOIN users 
            ON posts.userID = users.usersID
            WHERE forumID = ?;";
    $result = $this->executeQuery($sql, 'i', array($forumID));
    return $this->fetchResults($result);
  }
}