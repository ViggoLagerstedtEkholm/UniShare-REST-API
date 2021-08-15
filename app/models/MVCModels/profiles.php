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
    $this->insertOrUpdate($sql, 'si', array($image, $ID));
  }

  function addVisitor($ID, $user){
    $visits = $user["visits"];
    $updatedVisits = $visits + 1;
    $sql = "UPDATE users SET visits =? WHERE usersID = ?;";
    $this->insertOrUpdate($sql, 'ii', array($updatedVisits , $ID));
    return $updatedVisits;
  }

  function addVisitDate($ID){
    $sql = "UPDATE users SET lastOnline =? WHERE usersID = ?;";

    date_default_timezone_set("Europe/Stockholm");
    $Date = date("Y-m-d",time());

    $this->insertOrUpdate($sql, 'si', array($Date, $ID));
    return $Date;
  }
}
