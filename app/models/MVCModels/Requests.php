<?php
namespace App\Models\MVCModels;
use App\Models\MVCModels\Database;
use App\Models\Templates\Request;
use App\Includes\Validate;

class Requests extends Database implements IValidate{
  public function validate($params){
    $errors = array();
    if(Validate::arrayHasEmptyValue($params) === true){
      $errors[] = EMPTY_FIELDS;
    }

    if(!is_numeric($params["credits"]) || !is_numeric($params["duration"])){
      $errors[] = NOTNUMERIC;
    }

    return $errors;
  }

  function insertRequestedCourse($course, $userID){
    $sql = "INSERT INTO request (name, credits, duration, country, city, isHandled, university, description, userID) values(?,?,?,?,?,?,?,?,?);";
    $success = $this->insertOrUpdate($sql, 'siisssisi', array($course["name"], $course["credits"], $course["duration"], $course["country"], $course["city"], false, $course["university"], $course["description"], $userID));
    return $success;
  }

  function checkIfUserOwner($userID, $requestID){
    $sql = "SELECT requestID FROM request WHERE userID = ?;";
    $result = $this->executeQuery($sql, 'i', array($userID));

    while($row = $result->fetch_array())
    {
      if($row["requestID"] == $requestID){
        return true;
      }
    }
    return false;
  }

  function deleteRequest($requestID){
    $sql = "DELETE FROM request WHERE requestID = ?;";
    $this->executeQuery($sql, 'i', array($requestID));
  }

  function denyRequest($requestID){
    $sql = "UPDATE request SET isHandled = 1 WHERE requestID = ?;";
    $success = $this->insertOrUpdate($sql, 'i', array($requestID));
    return $success;
  }

  function approveRequest($requestID){
    try {
        $this->getConnection()->begin_transaction();
        $this->getConnection()->rollback();

        $sql = "SELECT * FROM request WHERE requestID = ?;";
        $result = $this->executeQuery($sql, 'i', array($requestID));
        $request = $result->fetch_assoc();

        date_default_timezone_set("Europe/Stockholm");
        $date = date('Y-m-d H:i:s');

        $sql = "INSERT INTO courses (name, credits, duration, added, country, city, university) values(?,?,?,?,?,?,?);";
        $inserted = $this->insertOrUpdate($sql, 'siissss', array($request["name"], $request["credits"], $request["duration"], $date, $request["country"], $request["city"], $request["university"]));
        if(!$inserted){
          $this->getConnection()->rollback();
        }

        $sql = "UPDATE request SET isHandled = 1 WHERE requestID = ?;";
        $updated = $this->insertOrUpdate($sql, 'i', array($requestID));
        if(!$updated){
          $this->getConnection()->rollback();
        }

        $this->getConnection()->commit();

    } catch (\Throwable $e) {
        $this->getConnection()->rollback();
        throw $e;
    }


    if($inserted && $updated){
      return true;
    }else{
      return false;
    }
  }

  function getRequestedCourses(){
    $sql = "SELECT * FROM request WHERE isHandled = 0;";
    $result = $this->executeQuery($sql);

    $requests = array();
    while($row = $result->fetch_assoc()){
      $request = new Request();
      $request->ID = $row['requestID'];
      $request->name = $row['name'];
      $request->credits = $row['credits'];
      $request->duration = $row['duration'];
      $request->country = $row['country'];
      $request->city = $row['city'];
      $request->university = $row['university'];
      $request->description = $row['description'];
      $request->userID = $row['userID'];
      $requests[] = $request;
    }
    return $requests;
  }
}
