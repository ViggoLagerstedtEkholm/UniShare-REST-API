<?php
namespace App\Includes\Projects;

require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/UniShare/vendor/autoload.php");

use \App\Controllers;
use \App\Models;

session_start();

if(isset($_POST["submit_project"])){
  $URL = $_POST["URL"];
  $description = $_POST["description"];
  $name = $_POST["name"];

  $fileSize = $_FILES['project-file']['size'];
  $fileErr = $_FILES['project-file']['error'];
  $file = $_FILES['project-file'];
  $fileTmpName = $_FILES['project-file']['tmp_name'];
  $fileType = $_FILES['project-file']['type'];

  $image_data = file_get_contents($_FILES['project-file']['tmp_name']);

  $project = new Models\Project($name, $description, $URL, $image_data);

  $projectController = new Controllers\ProjectsController();
  $projectController->connect_database();

  if(isset($_SESSION["userID"])){

    $allowed = array("image/jpeg", "image/gif", "image/png");

    $ID = $_SESSION["userID"];

    if(!in_array($fileType, $allowed)) {
      $error_message = 'Only jpg, gif, and png files are allowed.';
      header("location: ../profile.php?error=illegaltype");
      exit();
    }

    //Check if file upload had any errors.
    if($fileErr === 0){
      //Enable max file size. 500 000 bytes
      if($fileSize < 500000){
        $projectController->upload_project($project, $ID);
      }else{
        header("location: ../profile.php?error=filesizetoobig");
      }
    }else {
      header("location: ../profile.php?error=uploaderror");
    }
  }
  else{
    header("location: ../login.php");
  }
}else{
  header("location: ../profile.php?error=wrongsubmit");
  exit();
}
