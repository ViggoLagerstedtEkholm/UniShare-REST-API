<?php
namespace App\Models\MVCModels;
use App\Models;
use App\Models\MVCModels\Database;
use App\Core\Application;

class Profiles extends Database
{
  function uploadImage($image, $ID)
  {
    $sql = "UPDATE users SET userImage =? WHERE usersID = ?;";

    $stmt = mysqli_stmt_init($this->getConnection());
    mysqli_stmt_prepare($stmt, $sql);
    mysqli_stmt_bind_param($stmt, "si", $image, $ID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
  }

  function addVisitor($ID, $user){
    $visits = $user["visits"];

    $updatedVisits = $visits + 1;

    $sql = "UPDATE users SET visits =? WHERE usersID = ?;";

    $stmt = mysqli_stmt_init($this->getConnection());
    mysqli_stmt_prepare($stmt, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $updatedVisits , $ID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $updatedVisits;
  }

  function addVisitDate($ID){
    $sql = "UPDATE users SET lastOnline =? WHERE usersID = ?;";

    date_default_timezone_set("Europe/Stockholm");
    $Date = date("Y-m-d",time());

    $stmt = mysqli_stmt_init($this->getConnection());
    mysqli_stmt_prepare($stmt, $sql);
    mysqli_stmt_bind_param($stmt, "si", $Date, $ID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $Date;
  }
}
