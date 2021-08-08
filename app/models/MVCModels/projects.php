<?php
namespace App\Models\MVCModels;
use App\Models;
use App\Models\MVCModels\Database;
use App\Models\Project;

class Projects extends Database
{
 function DeleteProject($ID, $currentID){
    $sql = "DELETE FROM projects WHERE projectID = ?;";
    $stmt = mysqli_stmt_init($this->getConnection());

    if(!mysqli_stmt_prepare($stmt, $sql)){
      header("location: ../../profile?ID=$currentID&error=deleteprojectfail");
      exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $ID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("location: ../../profile?ID=$currentID");
    exit();
  }

 function GetMaxID(){
    if($this->getConnection()->connect_error){
      die('Connection Failed: ' . $this->getConnection()->connect_error);
    }else{
      $changedate = "";
        $sql = "SELECT MAX(projectID) FROM projects";
        $result = $this->getConnection()->query($sql)->fetch_assoc();
        return $result;
    }
    return 0;
  }

 function getProjects($ID){
    $projects = array();
    if($this->getConnection()->connect_error){
      die('Connection Failed: ' . $this->conn->connect_error);
    }else{
       $sql = "SELECT * FROM projects WHERE userID=?;";
       $stmt = $this->getConnection()->prepare($sql);
       $stmt->bind_param("i", $ID);
       $stmt->execute();
       $result = $stmt->get_result(); // get the mysqli result

       while ($row = $result->fetch_assoc())
       {
            $ID = $row["projectID"];
            $name = $row["name"];
            $description = $row["description"];
            $link = $row["link"];
            $image = $row["image"];

            $project = new Project();
            $project->ID = $ID;
            $project->name = $name;
            $project->description = $description;
            $project->link = $link;
            $project->image = base64_encode($image);
            $projects[] = $project;
        }
    }
    return $projects;
  }

  function uploadProject(Project $project, $ID){
    $sql = "INSERT INTO projects (name, description, link, userID, image) values (?,?,?,?,?);";
    $stmt = mysqli_stmt_init($this->getConnection());

    if(!mysqli_stmt_prepare($stmt, $sql)){
      header("location: ../../profile?ID=$ID&error=uploadqueryerror");
      exit();
    }

    $name = $project->name;
    $description = $project->description;
    $link = $project->link;
    $image = $project->image;

    mysqli_stmt_bind_param($stmt, "sssss", $name, $description, $link, $ID, $image);

    $result = mysqli_stmt_execute($stmt);
    if($result){
      header("location: ../../profile?ID=$ID");
      exit();
    }else{
      header("location: ../../profile?ID=$ID&error=uploadfail");
      exit();
    }
    mysqli_stmt_close($stmt);
  }
}
