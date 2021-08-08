<?php
namespace App\Models\MVCModels;
use App\Models;
use App\Models\MVCModels\Database;

class Profiles extends Database
{
  function uploadImage($image, $ID)
  {
    $sql = "UPDATE users SET userImage =? WHERE usersID = ?;";
    $stmt = mysqli_stmt_init($this->getConnection());

    if(!mysqli_stmt_prepare($stmt, $sql)){
      header("location: ../../profile?ID=$ID&error=uploadfail");
      exit();
    }

    mysqli_stmt_bind_param($stmt, "si", $image, $ID);
    mysqli_stmt_execute($stmt);

    if (!mysqli_stmt_execute($stmt))
    {
      header("location: ../../profile?ID=$ID&error=failinsertimage");
    }
    mysqli_stmt_close($stmt);

    header("location: ../../profile?ID=$ID");
    exit();
  }

  function addVisitor($ID, $user){
    $visits = $user["visits"];

    $updatedVisits = $visits + 1;

    $sql = "UPDATE users SET visits =? WHERE usersID = ?;";

    $stmt = mysqli_stmt_init($this->getConnection());

    if(!mysqli_stmt_prepare($stmt, $sql)){
      header("location: ./profile?error=visitcounterror");
      exit();
    }

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

    if(!mysqli_stmt_prepare($stmt, $sql)){
      header("location: ./profile?error=dateinserterror");
      exit();
    }

    mysqli_stmt_bind_param($stmt, "si", $Date, $ID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $Date;
  }
}
