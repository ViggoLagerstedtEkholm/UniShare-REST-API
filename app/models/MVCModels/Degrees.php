<?php
namespace App\Models\MVCModels;
use App\Models\Templates\Degree;

class Degrees extends Database
{
  function uploadDegree(Degree $degree, $ID){
    $sql = "INSERT INTO degrees (name, fieldOfStudy, userID, start_date, end_date) values(?,?,?,?,?);";

    $stmt = mysqli_stmt_init($this->getConnection());
    mysqli_stmt_prepare($stmt, $sql);

    $name = $degree->name;
    $fieldOfStudy = $degree->field_of_study;
    $start_date = $degree->start_date;
    $end_date = $degree->end_date;

    mysqli_stmt_bind_param($stmt, "sssss", $name, $fieldOfStudy, $ID, $start_date, $end_date);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
  }

  function deleteDegree($ID){
    $sql = "DELETE * FROM degrees WHERE degreeID = ?;";

    $stmt = mysqli_stmt_init($this->getConnection());
    mysqli_stmt_prepare($stmt, $sql);
    mysqli_stmt_bind_param($stmt, "s", $ID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
  }

  function getDegree($ID){
    $sql = "SELECT * FROM degrees WHERE userID = ?;";
    $stmt = mysqli_stmt_init($this->getConnection());
    mysqli_stmt_prepare($stmt, $sql);
    mysqli_stmt_bind_param($stmt, "s", $ID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    $data = $result->fetch_array();

    $degree = new Degree();
    $degree->ID = $data["degreeID"];
    $degree->name = $data["name"];
    $degree->field_of_study = $data["fieldOfStudy"];
    $degree->duration = $data["duration"];

    return $degree;
  }

  function insertDegreeCourse($degreeID, $courseID){
    $sql = "INSERT INTO degrees_courses (degreeID, courseID) values(?, ?);";
    $stmt = $this->getConnection()->prepare($sql);
    $stmt->bind_param("ii", $degreeID, $courseID);
    $stmt->execute();
  }

  function getDegrees($ID){
    $courses = array();
    $sql = "SELECT * FROM degrees WHERE userID = ?;";
    $stmt = $this->getConnection()->prepare($sql);
    $stmt->bind_param("i", $ID);
    $stmt->execute();
    $result = $stmt->get_result(); // get the mysqli result

    $degrees = array();

    while ($row = $result->fetch_assoc())
    {
         $degree = new Degree();
         $degree->ID = $row["degreeID"];
         $degree->name = $row["name"];
         $degree->field_of_study = $row["fieldOfStudy"];
         $degree->start_date = $row["start_date"];
         $degree->end_date = $row["end_date"];
         $degrees[] = $degree;
     }
     return $degrees;
  }
}
