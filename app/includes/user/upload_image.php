<?php
namespace App\Includes\User;

require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/UniShare/vendor/autoload.php");

session_start();

use \App\Controllers;

$didUpload = false;
if(file_exists($_FILES['file']['tmp_name']) || is_uploaded_file($_FILES['file']['tmp_name'])) {
    $didUpload = true;
}

if(isset($_POST["submit_image"]) && $didUpload)
{
  if(isset($_SESSION["userID"])){
    $profileController = new Controllers\ProfileController();
    $profileController->connect_database();

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
      header("location: ../../profile.php?ID=$ID&error=illegaltype");
      exit();
    }

    //Check if file upload had any errors.
    if($fileErr === 0){
      //Enable max file size. 500 000 bytes
      if($fileSize < 2000000){
        $profileController->upload_image($image_data, $ID);
        $profileController->close_database();
      }else{
        header("location: ../../views/profile.php?ID=$ID&error=filesizetoobig");
        $profileController->close_database();
      }
    }else {
      header("location: ../../views/profile.php?ID=$ID&error=uploaderror");
      $profileController->close_database();
    }
  }
  else{
    header("location: ../../views/login.php");
  }
}else{
  if(isset($_SESSION["userID"])){
    $ID = $_SESSION["userID"];
    header("location: ../../views/profile.php?ID=$ID&error=wrongsubmit");
  }
  exit();
}
