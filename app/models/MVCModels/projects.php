<?php
namespace App\Models\MVCModels;
use App\Models\MVCModels\Database;
use App\Includes\Validate;

class Projects extends Database implements IValidate{
  public function validate($params){
    $errors = array();

    if(Validate::arrayHasEmptyValue($params) === true){
      $errors[] = EMPTY_FIELDS;
    }

    if(Validate::hasInvalidProjectLink($params["link"]) === true){
      $errors[] = INVALID_PROJECT_LINK;
    }

    if(!Validate::hasValidUpload($params['project-file']) && $params['customCheck'] == "Off"){
      $errors[] = INVALID_IMAGE;
    }

    return $errors;
  }

  function checkIfUserOwner($userID, $projectID){
    $sql = "SELECT projectID FROM projects WHERE userID = ?;";
    $result = $this->executeQuery($sql, 'i', array($userID));

    while($row = $result->fetch_array())
    {
      if($row["projectID"] == $projectID){
        return true;
      }
    }
    return false;
  }

 function DeleteProject($projectID){
    $sql = "DELETE FROM projects WHERE projectID = ?;";
    $this->delete($sql, 'i', array($projectID));
  }

 function GetMaxID(){
    $sql = "SELECT MAX(projectID) FROM projects";
    $result = $this->executeQuery($sql);
    return $result->fetch_assoc();
  }

 function getProjects($ID){
   $sql = "SELECT * FROM projects WHERE userID=?;";
   $result = $this->executeQuery($sql, 'i', array($ID));
   return $this->fetchResults($result);
  }

  function getProject($ID){
    $sql = "SELECT * FROM projects WHERE projectID = ?;";
    $result = $this->executeQuery($sql, 'i', array($ID));
    return $result->fetch_assoc();
  }

  function updateProject($ID, $params, $image){
    $sql = "UPDATE projects
            SET name = ?, description = ?, link = ?, image = ?
            WHERE projectID = ?;";
    $this->insertOrUpdate($sql, 'ssssi', array($params["name"], $params["description"], $params["link"], $image, $ID));
  }

  function uploadProject($params, $ID, $image){
    $sql = "INSERT INTO projects (name, description, link, userID, image, added) values (?,?,?,?,?,?);";
    date_default_timezone_set("Europe/Stockholm");
    $date = date('Y-m-d H:i:s');

    $result = $this->insertOrUpdate($sql, 'ssssss', array($params["name"], $params["description"], $params["link"], $ID, $image, $date));
  }
}
