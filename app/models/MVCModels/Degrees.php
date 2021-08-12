<?php
namespace App\Models\MVCModels;
use App\Models\Templates\Degree;

class Degrees extends Database
{
  function uploadDegree(Degree $degree, $ID){
    $sql = "INSERT INTO degrees (name, fieldOfStudy, duration) values(?,?,?);";

    $stmt = mysqli_stmt_init($this->getConnection());
    mysqli_stmt_prepare($stmt, $sql);

    $name = $degree->name;
    $fieldOfStudy = $degree->field_of_study;
    $duration = $degree->duration;

    mysqli_stmt_bind_param($stmt, "sss", $name, $fieldOfStudy, $duration);
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

  }

  function getDegrees(){

  }
}
