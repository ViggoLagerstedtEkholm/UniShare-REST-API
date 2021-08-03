<?php
if(isset($_POST["submit_image"]) && isset($_FILES['file']))
{
  session_start();

  if(isset($_SESSION["userID"])){
    $fileSize = $_FILES['file']['size'];
    $fileErr = $_FILES['file']['error'];
    $file = $_FILES['file'];
    $fileTmpName = $_FILES['file']['tmp_name'];
    $fileType = $_FILES['file']['type'];

    $image_data = file_get_contents($_FILES['file']['tmp_name']);
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
        require_once 'helper_functions-inc.php';
        require_once 'database-inc.php';

        $database = new Database('localhost', 'root', '', '9.0');

        uploadImage($database->getConn(), $image_data, $ID);
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
