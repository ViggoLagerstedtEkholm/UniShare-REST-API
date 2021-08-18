<?php
namespace App\Models\MVCModels;
use App\Models\MVCModels\Database;
use App\Models\Templates\Comment;

class Comments extends Database{
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
  
  function getComments($profileID){
    $sql = "SELECT profileComment.*, userImage, userDisplayName
            FROM profileComment
            JOIN users
            ON author = usersID
            WHERE profile = ?;";
    $result = $this->executeQuery($sql, 'i', array($profileID));
  
    $comments = array();
    while( $row = $result->fetch_array())
    {
        $comment = new Comment();
        $comment->ID = $row['commentID'];
        $comment->text = $row['text'];
        $comment->date = $row['date'];
        $comment->author = $row['author'];
        $comment->profile = $row['profile'];
        $comment->added = $row['date'];
        $comment->image = $row["userImage"];
        $comment->display_name = $row["userDisplayName"];
  
        $comments[] = $comment;
    }
    return $comments;
  }
}
