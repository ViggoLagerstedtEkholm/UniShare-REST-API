<?php
namespace App\Models\MVCModels;
use App\Models\Templates\Degree;
use App\Models\Templates\Course;
use App\Includes\Validate;
use App\Core\Session;

class Degrees extends Database implements IValidate{
  public function validate($params){
    $errors = array();

    if(Validate::arrayHasEmptyValue($params) === true){
      $errors[] = EMPTY_FIELDS;
    }

    if(Validate::hasInvalidDates($params["start_date"], $params["end_date"]) === true){
      $errors[] = INVALID_DATES;
    }

    return $errors;
  }

  function userHasDegreeID($newActiveDegreeID){
    $sql = "SELECT degreeID FROM degrees
            JOIN users
            ON degrees.userID = users.usersID
            WHERE usersID = ?;";

    $ID = Session::get(SESSION_USERID);

    $result = $this->executeQuery($sql, 'i', array($ID));

    $IDs = array();
    while( $row = $result->fetch_array()){
       $IDs[] = $row["degreeID"];
    }

    $exists = in_array($newActiveDegreeID, $IDs);

    if($exists){
      return true;
    }else{
      return false;
    }
  }

  function deleteCourseFromDegree($degreeID, $courseID){
    $sql = "DELETE FROM degrees_courses
            WHERE degreeID = ? AND courseID = ?;";

    $this->executeQuery($sql, 'ii', array($degreeID, $courseID));
  }

  function checkIfUserOwner($userID, $degreeID){
    $sql = "SELECT degreeID FROM degrees
            JOIN users
            WHERE userID = ?;";
    $result = $this->executeQuery($sql, 'i', array($userID));

    while($row = $result->fetch_array())
    {
      if($row["degreeID"] == $degreeID){
        return true;
      }
    }
    return false;
  }

  function uploadDegree($params, $ID){
    $sql = "INSERT INTO degrees (name, fieldOfStudy, userID, start_date, end_date, country, city, university) values(?,?,?,?,?,?,?,?);";
    $result = $this->insertOrUpdate($sql, 'ssisssss', array($params["name"], $params["field_of_study"], $ID,
    $params["start_date"], $params["end_date"], $params["country"], $params["city"], $params["university"]));
  }

  function updateDegree($params, $userID){
    $sql = "UPDATE degrees SET name = ?, fieldOfStudy = ?, start_date = ?, end_date = ?, country = ?, city = ?, university = ? WHERE userID = ?;";
    $this->insertOrUpdate($sql, 'sssssssi', array($params["name"], $params["field_of_study"],
    $params["start_date"], $params["end_date"], $params["country"], $params["city"], $params["university"], $userID));
  }

  function deleteDegree($ID){
    $sql = "DELETE FROM degrees WHERE degreeID = ?;";
    $this->executeQuery($sql, 'i', array($ID));
  }

  function getDegree($ID){
    $sql = "SELECT * FROM degrees WHERE degreeID = ?;";
    $result = $this->executeQuery($sql, 'i', array($ID));
    $data = $result->fetch_array();

    $degree = new Degree();
    $degree->ID = $data["degreeID"];
    $degree->name = $data["name"];
    $degree->field_of_study = $data["fieldOfStudy"];
    $degree->start_date = $data["start_date"];
    $degree->end_date = $data["end_date"];
    $degree->country = $data["country"];
    $degree->city = $data["city"];
    $degree->university = $data["university"];

    return $degree;
  }

  function getCoursesDegree($degreeID){
    $sql = "SELECT * FROM courses
            JOIN degrees_courses
            ON courses.courseID = degrees_courses.courseID
            WHERE degreeID = ?;";

    $result = $this->executeQuery($sql, 'i', array($degreeID));

    $courses = array();

    while ($row = $result->fetch_assoc())
    {
        $course = new Course();
        $course->ID = $row['courseID'];
        $course->name = $row['name'];
        $course->credits = $row['credits'];
        $course->duration = $row['duration'];
        $course->added = $row['added'];
        $course->country = $row['country'];
        $course->city = $row['city'];
        $course->university = $row['university'];
        $courses[] = $course;
     }
     return $courses;
  }

  function getDegrees($ID){
    $courses = array();
    $sql = "SELECT * FROM degrees WHERE userID = ?;";
    $result = $this->executeQuery($sql, 'i', array($ID));

    $degrees = array();
    while ($row = $result->fetch_assoc())
    {
         $degree = new Degree();
         $degree->ID = $row["degreeID"];
         $degree->name = $row["name"];
         $degree->field_of_study = $row["fieldOfStudy"];
         $degree->start_date = $row["start_date"];
         $degree->end_date = $row["end_date"];
         $degree->country = $row["country"];
         $degree->city = $row["city"];
         $degree->university = $row["university"];

         $courses = $this->getCoursesDegree($degree->ID);
         $degree->courses = $courses;
         $degrees[] = $degree;
     }
     return $degrees;
  }
}
