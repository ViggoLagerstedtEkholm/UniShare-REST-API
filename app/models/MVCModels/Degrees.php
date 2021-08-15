<?php
namespace App\Models\MVCModels;
use App\Models\Templates\Degree;
use App\Models\Templates\Course;

class Degrees extends Database
{
  function uploadDegree(Degree $degree, $ID){
    $sql = "INSERT INTO degrees (name, fieldOfStudy, userID, start_date, end_date) values(?,?,?,?,?);";
    $result = $this->insertOrUpdate($sql, 'sssss', array($degree->name, $degree->field_of_study, $ID, $degree->start_date, $degree->end_date));
  }

  function deleteDegree($ID){
    $sql = "DELETE * FROM degrees WHERE degreeID = ?;";
    $this->delete($sql, 'i', array($ID));
  }

  function getDegree($ID){
    $sql = "SELECT * FROM degrees WHERE userID = ?;";
    $result = $this->executeQuery($sql, 'i', array($ID));
    $data = $result->fetch_array();

    $degree = new Degree();
    $degree->ID = $data["degreeID"];
    $degree->name = $data["name"];
    $degree->field_of_study = $data["fieldOfStudy"];
    $degree->duration = $data["duration"];

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
         $course->ID = $row["courseID"];
         $course->name = $row["name"];
         $course->credits = $row["credits"];
         $course->duration = $row["duration"];
         $course->added = $row["added"];
         $course->fieldOfStudy = $row["fieldOfStudy"];
         $course->location = $row["location"];

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

         $courses = $this->getCoursesDegree($degree->ID);
         $degree->courses = $courses;
         $degrees[] = $degree;
     }
     return $degrees;
  }
}
