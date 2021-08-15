<?php
namespace App\Models\MVCModels;
use App\Models;
use App\Models\MVCModels\Database;
use App\Models\Templates\Comment;
use App\Core\Application;

class Profiles extends Database
{
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
    $sql = "UPDATE users SET lastOnline =? WHERE usersID = ?;";

    date_default_timezone_set("Europe/Stockholm");
    $date = date("Y-m-d",time());

    $this->insertOrUpdate($sql, 'si', array($date, $ID));
    return $date;
  }

  function addComment($posterID, $profileID, $comment){
    date_default_timezone_set("Europe/Stockholm");
    $date = date("Y-m-d",time());

    $sql = "INSERT INTO profileComment (text, date, author, profile) values(?,?,?,?);";

    return $this->insertOrUpdate($sql, 'ssii', array($comment, $date, $posterID, $profileID));
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
