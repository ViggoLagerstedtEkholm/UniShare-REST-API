<?php
namespace App\Includes\Projects;

require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/UniShare/vendor/autoload.php");

session_start();

use \App\Controllers;

if(isset($_SESSION["userID"])){
  $projectsController = new Controllers\ProjectsController();
  $projectsController->connect_database();

  $MAXID = $projectsController->get_max_id();

  if(isset($_SESSION["userID"])){
    for($i = 0; $i <= $MAXID; $i++){
        if(isset($_POST[$i])){
            //Delete the feed if it matches the ID of the clicked feed.
            $projectsController->delete_project($i, $_SESSION["userID"]);
        }
    }
  }else{
    header("location: ../login.php");
    exit();
  }
}
