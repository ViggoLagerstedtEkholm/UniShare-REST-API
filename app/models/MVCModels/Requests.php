<?php
namespace App\Models\MVCModels;
use App\Models\MVCModels\Database;
use App\Models\Templates\Course;
use App\Includes\Validate;

class Requests extends Database implements IValidate{
  public function validate($params){
    $errors = array();
    if(Validate::arrayHasEmptyValue($params) === true){
      $errors[] = EMPTY_FIELDS;
    }
    if(is_numeric($params["credits"]) && is_numeric($params["duration"])){
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

  function getRequestedCourses(){
    $sql = "SELECT * FROM request";
    $result = $this->executeQuery($sql);
    
    $requests = array();
    while($row = $result->fetch_assoc()){
      $request = new Course();
      $request->ID = $row['requestID'];
      $request->name = $row['name'];
      $request->credits = $row['credits'];
      $request->duration = $row['duration'];
      $request->country = $row['country'];
      $request->city = $row['city'];
      $request->university = $row['university'];
      $requests[] = $request;
    }
    return $requests;
  }
}
