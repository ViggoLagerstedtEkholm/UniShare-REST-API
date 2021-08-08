<?php
use App\Core\Session;

$html = file_get_contents('app/views/html/profile.html');
$fragments = explode("<!--===edit===-->", $html);
//print_r($fragments);

$sessionID = false;
if(Session::isLoggedIn()){
  $sessionID = Session::get('userID');
}

if($image == ""){
    echo str_replace('---SRC---', "images/user.png", $fragments[0]);
  }else{
    echo str_replace('---SRC---', $user_image, $fragments[0]);
}
if($sessionID != false){
  if($currentPageID == $sessionID){
    echo $fragments[1];
  }
}

$info_panel = str_replace('---First_name---', $first_name, $fragments[2]);
$info_panel = str_replace('---Last_name---', $last_name, $info_panel);
$info_panel = str_replace('---Date---', $visitDate, $info_panel);
$info_panel = str_replace('---Visits---', $updatedVisitCount , $info_panel);
$info_panel = str_replace('---Added_projects---', count($projects) , $info_panel);
$info_panel =  str_replace('---Completed_courses---', 0 , $info_panel);
echo $info_panel;

if($sessionID != false){
  if($currentPageID == $sessionID){
    echo$fragments[3];
  }
}

echo $fragments[4];

foreach ($projects as $item) {
  $projectID = $item->ID;
  $name = $item->name;
  $description = $item->description;
  $link = $item->link;
  $image = $item->image;

  $project_image = 'data:image/jpeg;base64,'.$image;

  $length = strlen($name);
  $project_panel = str_replace('---name---', $length > 10 ? substr($name, 0, 10) . "..." : $name, $fragments[5]);
  $project_panel = str_replace('---ID---', $projectID , $project_panel);
  $project_panel = str_replace('---PROJECT-SRC---', $project_image , $project_panel);
  echo $project_panel;

  if($sessionID != false){
    if($currentPageID == $sessionID){
      $project_panel = str_replace('---ID---', $projectID , $fragments[6]);
      $project_panel = str_replace('---LINK---', $link , $project_panel);
      echo $project_panel;
    }
  }
  echo $fragments[7];
}

echo $fragments[8];

if($sessionID != false){
  if($currentPageID == $sessionID){
    echo $fragments[9];
  }
}

echo$fragments[10];
echo$fragments[11];
echo$fragments[12];
