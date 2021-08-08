<?php
namespace App\Models\MVCModels;

require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/UniShare/vendor/autoload.php");

use App\Models;
use App\Models\MVCModels\Database;

class Profiles extends Database
{
  function uploadImage($image, $ID)
  {
    $this->connect();
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
    $this->close();
    exit();
  }

  function addVisitor($ID, $user){
    $this->connect();
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
    $this->close();
    return $updatedVisits;
  }

  function addVisitDate($ID){
    $this->connect();
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
    $this->close();
    return $Date;
  }
}
