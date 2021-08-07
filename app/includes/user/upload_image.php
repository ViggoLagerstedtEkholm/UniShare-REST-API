<?php
namespace App\Includes\User;

require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/UniShare/vendor/autoload.php");

session_start();

use \App\Controllers;
use \App\Includes;

if(Includes\Validate::hasInvalidUpload($_FILES['file']['tmp_name']) !== false){
  redirect("?error=noupload");
}

if(isset($_POST["submit_image"]))
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

    if(Includes\Validate::hasInvalidImageExtension($fileType) !== false){
      header("location: ../../profile.php?ID=$ID&error=illegaltype");
      exit();
    }

    $ID = $_SESSION["userID"];

    //Check if file upload had any errors.
    if($fileErr === 0){
      //Enable max file size. 500 000 bytes
      if($fileSize < 20000000){
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
