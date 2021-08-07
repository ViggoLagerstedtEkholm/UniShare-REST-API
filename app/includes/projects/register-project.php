<?php
namespace App\Includes\Projects;

require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/UniShare/vendor/autoload.php");

use \App\Controllers;
use \App\Models;
use App\Includes;

session_start();

if(Includes\Validate::hasInvalidUpload($_FILES['project-file']['tmp_name']) !== false){
  redirect("?error=noupload");
}

if(isset($_POST["submit_project"])){
  $fileSize = $_FILES['project-file']['size'];
  $fileErr = $_FILES['project-file']['error'];
  $file = $_FILES['project-file'];
  $fileTmpName = $_FILES['project-file']['tmp_name'];
  $fileType = $_FILES['project-file']['type'];

  $image_data = file_get_contents($_FILES['project-file']['tmp_name']);
  $URL = $_POST["URL"];
  $description = $_POST["description"];
  $name = $_POST["name"];

  $project = new Models\Project($name, $description, $URL, $image_data);

  if(Includes\Validate::hasInvalidImageExtension($fileType) !== false){
    redirect("error=invalidExtension");
  }
  if(Includes\Validate::hasEmptyProject($project) !== false){
    redirect("error=emptyfield");
  }

  $projectController = new Controllers\ProjectsController();
  $projectController->connect_database();

  if(isset($_SESSION["userID"]))
  {
    $ID = $_SESSION["userID"];
    //Check if file upload had any errors.
    if($fileErr === 0){
      //Enable max file size. 500 000 bytes
      if($fileSize < 500000){
        $projectController->upload_project($project, $ID);
      }else{
        redirect("error=filesizetoobig");
      }
    }else {
      redirect("error=uploaderror");
    }
  }
  else{
    //redirect("error=invalidsession");
  }
}else{
  redirect("error=invalidsubmit");
  exit();
}

function redirect($error){
  if(isset($_SESSION["userID"])){
    $ID = $_SESSION["userID"];
    header("location: ../../views/view/profile.php?ID=". $ID . '&' . $error);
  }else{

  }
  exit();
}
