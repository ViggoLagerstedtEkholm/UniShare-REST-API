<?php
namespace App\Models\MVCModels;
use App\Models\MVCModels\Database;
use App\Includes\Validate;

class Comments extends Database implements IValidate{
  public function validate($params){
    $errors = array();

    if(Validate::arrayHasEmptyValue($params) === true){
      $errors[] = EMPTY_FIELDS;
    }

    return $errors;
  }

  function getCommentCount($userID){
     $sql = "SELECT Count(*) FROM profilecomment WHERE profile = ?;";
     $result = $this->executeQuery($sql, 'i', array($userID));
     return $result->fetch_assoc()["Count(*)"];
  }

  function addComment($posterID, $profileID, $comment){
    date_default_timezone_set("Europe/Stockholm");
    $date = date("Y-m-d",time());

    $sql = "INSERT INTO profileComment (text, date, author, profile) values(?,?,?,?);";

    return $this->insertOrUpdate($sql, 'ssii', array($comment, $date, $posterID, $profileID));
  }

  function checkIfUserAuthor($userID, $commentIDtoDelete){
    $sql = "SELECT commentID FROM profilecomment WHERE author = ?;";
    $result = $this->executeQuery($sql, 'i', array($userID));

    while($row = $result->fetch_array())
    {
      if($row["commentID"] == $commentIDtoDelete){
        return true;
      }
    }
    return false;
  }

  function deleteComment($commentID){
    $sql = "DELETE FROM profilecomment WHERE commentID = ?;";
    $this->executeQuery($sql, 'i', array($commentID));
  }

  function getComments($from, $to, $profileID){
    $sql = "SELECT profileComment.*, userImage, userDisplayName
            FROM profileComment
            JOIN users
            ON author = usersID
            WHERE profile = ?
            LIMIT ?, ?;";
    $result = $this->executeQuery($sql, 'iii', array($profileID, $from, $to));
    return $this->fetchResults($result);
  }
}
