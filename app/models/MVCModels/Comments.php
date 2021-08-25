<?php
namespace App\Models\MVCModels;
use App\Models\MVCModels\Database;
use App\Includes\Validate;

/**
 * Model for querying comments.
 * @author Viggo Lagestedt Ekholm
 */
class Comments extends Database implements IValidate{
  /**
   * Check if the user input is sufficient enough.
   * @param params array
   */
  public function validate($params){
    $errors = array();

    if(Validate::arrayHasEmptyValue($params) === true){
      $errors[] = EMPTY_FIELDS;
    }

    return $errors;
  }

  /**
   * Get the amount of total comments in the comments table.
   * @param userID int
   * @return int count.
   */
  function getCommentCount($userID){
     $sql = "SELECT Count(*) FROM profilecomment WHERE profile = ?;";
     $result = $this->executeQuery($sql, 'i', array($userID));
     return $result->fetch_assoc()["Count(*)"];
  }

  /**
   * Add a comment to the database.
   * @param posterID int
   * @param profileID int
   * @param comment string
   * @return bool
   */
  function addComment($posterID, $profileID, $comment){
    date_default_timezone_set("Europe/Stockholm");
    $date = date("Y-m-d",time());

    $sql = "INSERT INTO profileComment (text, date, author, profile) values(?,?,?,?);";

    return $this->insertOrUpdate($sql, 'ssii', array($comment, $date, $posterID, $profileID));
  }

  /**
   * Check if a given user ID is the author of a certain comment.
   * @param userID int
   * @param commentIDtoDelete int
   * @return bool
   */
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

  /**
   * Delete comment after ID.
   * @param commentID int
   */
  function deleteComment($commentID){
    $sql = "DELETE FROM profilecomment WHERE commentID = ?;";
    $this->executeQuery($sql, 'i', array($commentID));
  }

  /**
   * Get the comments from a given interval.
   * @param from int
   * @param to int
   * @param profileID int
   * @return array
   */
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
