<?php
include_once 'header.php';
require_once 'includes/helper_functions-inc.php';
require_once 'includes/database-inc.php';

if(isset($_GET["ID"])){
  $ID = $_GET["ID"];
  $database = new Database('localhost', 'root', '', '9.0');
  $updatedVisitCount = addVisitor($database->getConn(), $ID);

  $Image = getImage($database->getConn(), $ID);
  $user = usernameExists($database->getConn(), $ID, $ID);
  $projects = getProjects($database->getConn(), $ID);

  $user_image = 'data:image/jpeg;base64,'.$Image;
  $html = file_get_contents("html/profile.html");
  $fragments = getPieces($html, "<!--===edit===-->");
  
  if($Image == ""){
    echo str_replace('---SRC---', "images/user.png", $fragments[0]);
  }else{
    echo str_replace('---SRC---', $user_image, $fragments[0]);
  }

  $info_panel = str_replace('---First_name---', $user["userFirstName"], $fragments[2]);
  $info_panel = str_replace('---Last_name---', $user["userLastName"], $info_panel);

  if(isset($_SESSION["userID"])){
    if($ID == $_SESSION["userID"]){
      $date = addVisitDate($database->getConn(), $ID);
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

  echo$fragments[4];

  foreach ($projects as $item) {
    $projectID = $item->getID();
    $name = $item->getName();
    $description = $item->getDescription();
    $link = $item->getLink();
    $image = $item->getImage();

    $project_image = 'data:image/jpeg;base64,'.$image;

    $project_panel = str_replace('---name---', $name , $fragments[5]);
    $project_panel = str_replace('---description---', $description , $project_panel);
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

}else{
  header("location: /index.php?ID=" );
}

function getPieces($html){
  //Get the segments from the html file we want to modify.
  return explode("<!--===edit===-->", $html);
}
