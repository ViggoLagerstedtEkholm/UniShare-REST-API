<?php
require_once 'project-inc.php';
require_once 'database-inc.php';
require_once 'helper_functions-inc.php';

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

  $project = new Project($name, $description, $URL, $image_data);

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

        $database = new Database('localhost', 'root', '', '9.0');

        uploadProject($database->getConn(), $project, $ID);
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
