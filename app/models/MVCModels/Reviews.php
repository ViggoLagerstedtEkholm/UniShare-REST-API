<?php
namespace App\Models\MVCModels;
use App\Models\MVCModels\Database;
use App\Includes\Validate;
use App\Core\Session;

class Reviews extends Database implements IValidate{
  public function validate($params){
    $errors = array();

    if(Validate::arrayHasEmptyValue($params) === true){
      $errors[] = EMPTY_FIELDS;
    }

    if(strlen($params["text"]) < 200){
      $errors[] = "DESCRIPTION_CHARS_NOT_ENOUGH";
    }

    return $errors;
  }

  function getReviewCount($courseID){
    $sql = "SELECT Count(*) FROM review WHERE courseID = ?;";
    $result = $this->executeQuery($sql, 'i', array($courseID));
    return $result->fetch_assoc()["Count(*)"];
  }

  function getReview($userID, $courseID){
    $sql = "SELECT * FROM review WHERE userID = ? AND courseID = ?;";
    $result = $this->executeQuery($sql, 'ii', array($userID, $courseID));
    return $result->fetch_assoc();
  }

  function getReviews($from, $to, $courseID){
    $sql = "SELECT review.*, userDisplayName, userImage
            FROM review
            JOIN users
            ON review.userID = users.usersID
            WHERE courseID = ?
            LIMIT ?,?";
    $result = $this->executeQuery($sql, 'iii', array($courseID, $from, $to));
    return $this->fetchResults($result);
  }

  function insertReview($params){
    $sql = "INSERT INTO review (userID, courseID, text, fulfilling, environment, difficulty, grading, litterature, overall)
    values(?,?,?,?,?,?,?,?,?)
    ON DUPLICATE KEY UPDATE text = ?, fulfilling = ?, environment = ?, difficulty = ?, grading = ?, litterature = ?, overall = ?;";

    $userID = Session::get(SESSION_USERID);

    $success = $this->insertOrUpdate($sql, 'iisiiiiiisiiiiii', array(
    $userID, $params["courseID"], $params["text"],
    $params["fulfilling"], $params["environment"], $params["difficulty"],
    $params["grading"], $params["litterature"], $params["overall"],
    $params["text"], $params["fulfilling"], $params["environment"],
    $params["difficulty"], $params["grading"], $params["litterature"], $params["overall"]));

    return $success;
  }

  function deleteReview($userID, $courseID){
    $sql = "DELETE FROM review WHERE userID = ? AND courseID = ?;";
    $this->executeQuery($sql, 'ii', array($userID, $courseID));
  }
}
