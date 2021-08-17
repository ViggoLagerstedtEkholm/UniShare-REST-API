<?php
namespace App\Models\MVCModels;
use App\Models\MVCModels\Database;
use App\Models\Templates\Project;

class Projects extends Database{
 function DeleteProject($ID, $currentID){
    $sql = "DELETE FROM projects WHERE projectID = ?;";
    $this->delete($sql, 'i', array($ID));
  }

 function GetMaxID(){
    $sql = "SELECT MAX(projectID) FROM projects";
    $result = $this->executeQuery($sql);
    return $result->fetch_assoc();
  }

 function getProjects($ID){
   $sql = "SELECT * FROM projects WHERE userID=?;";
   $result = $this->executeQuery($sql, 'i', array($ID));

   $projects = array();
   while ($row = $result->fetch_assoc())
   {
        $project = new Project();
        $project->ID = $row["projectID"];
        $project->name = $row["name"];
        $project->description = $row["description"];
        $project->link = $row["link"];
        $project->image = base64_encode($row["image"]);
        $projects[] = $project;
    }
    return $projects;
  }

  function getProject($ID){
    $sql = "SELECT * FROM projects WHERE projectID = ?;";
    $result = $this->executeQuery($sql, 'i', array($ID));
    return $result->fetch_assoc();
  }

  function uploadProject(Project $project, $ID){
    $sql = "INSERT INTO projects (name, description, link, userID, image) values (?,?,?,?,?);";
    $result = $this->insertOrUpdate($sql, 'sssss', array($project->name, $project->description, $project->link, $ID, $project->image));

    if($result){
      return true;
    }else{
      return false;
    }
  }
}
