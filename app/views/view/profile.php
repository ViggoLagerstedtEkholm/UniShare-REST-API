<?php
//include_once 'header.php';

require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/UniShare/vendor/autoload.php");

use \App\Controllers;

if(isset($_GET["ID"])){
  $html = file_get_contents("../html/profile.html");
  $fragments = getPieces($html, "<!--===edit===-->");

  $usersController = new Controllers\UsersController();
  $projectsController = new Controllers\ProjectsController();
  $profileController = new Controllers\ProfileController();

  $usersController->connect_database();
  $projectsController->connect_database();
  $profileController->connect_database();

  $ID = $_GET["ID"];

  $user = $usersController->get_user($ID);
  $image = base64_encode($user["userImage"]);
  $updatedVisitCount = $profileController->add_visitor($ID, $user);
  $user = $usersController->username_exists($ID, $ID);
  $projects = $projectsController->get_projects($ID);
  $user_image = 'data:image/jpeg;base64,' . $image;

  if($image == ""){
    echo str_replace('---SRC---', "../../../images/user.png", $fragments[0]);
  }else{
    echo str_replace('---SRC---', $user_image, $fragments[0]);
  }

  $info_panel = str_replace('---First_name---', $user["userFirstName"], $fragments[2]);
  $info_panel = str_replace('---Last_name---', $user["userLastName"], $info_panel);

  if(isset($_SESSION["userID"])){
    if($ID == $_SESSION["userID"]){
      $date = $profileController->add_visit_date($ID);
      echo $fragments[1];
      $info_panel = str_replace('---Date---', $date, $info_panel);
    }else{
      echo "-";
      $date = $user["lastOnline"];
      $info_panel = str_replace('---Date---', $date, $info_panel);
    }
  }else{
    echo "-";
    $date = $user["lastOnline"];
    $info_panel = str_replace('---Date---', $date, $info_panel);
  }

  $info_panel = str_replace('---Visits---', $updatedVisitCount , $info_panel);
  $info_panel = str_replace('---Added_projects---', count($projects) , $info_panel);
  $info_panel =  str_replace('---Completed_courses---', 0 , $info_panel);

  echo $info_panel;

  if(isset($_SESSION["userID"])){
    if($ID == $_SESSION["userID"]){
      echo$fragments[3];
    }
  }

  echo $fragments[4];

  foreach ($projects as $item) {
    $projectID = $item->getID();
    $name = $item->getName();
    $description = $item->getDescription();
    $link = $item->getLink();
    $image = $item->getimage();

    $project_image = 'data:image/jpeg;base64,'.$image;

    $length = strlen($name);
    $project_panel = str_replace('---name---', $length > 10 ? substr($name, 0, 10) . "..." : $name, $fragments[5]);
    $project_panel = str_replace('---PROJECT-SRC---', $project_image , $project_panel);
    $project_panel = str_replace('---LINK---', $link , $project_panel);
    $project_panel = str_replace('---ID---', $projectID , $project_panel);

    echo $project_panel;
  }

  echo$fragments[6];

  if(isset($_SESSION["userID"])){
    if($ID == $_SESSION["userID"]){
      echo $fragments[7];
    }
  }

  echo$fragments[8];
  echo$fragments[9];
  echo$fragments[10];

}else{
  header("location: startpage.php");
}

function getPieces($html){
  //Get the segments from the html file we want to modify.
  return explode("<!--===edit===-->", $html);
}

$usersController->close_database();
$projectsController->close_database();
$profileController->close_database();
