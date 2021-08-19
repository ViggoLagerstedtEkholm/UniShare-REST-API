<?php
use App\Core\Session;
$html = file_get_contents('app/views/profile/profile.html');
$fragments = explode("<!--===edit===-->", $html);
//print_r($fragments);
//echo $html;
$sessionID = false;
if(Session::isLoggedIn()){
  $sessionID = Session::get('userID');
}

if($image == ""){
    echo str_replace('---SRC---', "images/user.png", $fragments[0]);
  }else{
    echo str_replace('---SRC---', 'data:image/jpeg;base64,'.$image, $fragments[0]);
}

if($sessionID != false){
  if($currentPageID == $sessionID){
    echo $fragments[1];
  }
}

$info_panel = str_replace('---First_name---', $first_name, $fragments[2]);
$info_panel = str_replace('---display_name---', $display_name, $info_panel);
$info_panel = str_replace('---Last_name---', $last_name, $info_panel);
$info_panel = str_replace('---Date---', $visitDate, $info_panel);
$info_panel = str_replace('---Visits---', $updatedVisitCount , $info_panel);
$info_panel = str_replace('---Added_projects---', count($projects) , $info_panel);
$info_panel =  str_replace('---Completed_courses---', 0 , $info_panel);
$info_panel =  str_replace('---privilege---', $privilege , $info_panel);
$info_panel =  str_replace('---description---', $description , $info_panel);


echo $info_panel;

if($sessionID != false){
  if($currentPageID == $sessionID){
    echo $fragments[3];
  }
}

echo $fragments[4];

foreach ($projects as $project) {
  $project_panel = str_replace('---name---', $project->name, $fragments[5]);
  $project_panel = str_replace('---ID---', $project->ID, $project_panel);
  $project_panel = str_replace('---PROJECT-SRC---', 'data:image/jpeg;base64,'.$project->image, $project_panel);
  $project_panel = str_replace('---LINK---', $project->link, $project_panel);
  $project_panel = str_replace('---description---', $project->description, $project_panel);
  $project_panel = str_replace('---date---', $project->added, $project_panel);


  echo $project_panel;

  if($sessionID != false){
    if($currentPageID == $sessionID){
      echo str_replace('---ID---', $project->ID , $fragments[6]);
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

echo $fragments[10];

foreach($degrees as $degree){
  echo $fragments[11];

  $temp = str_replace('---degree_name---', $degree->name, $fragments[12]);
  $temp = str_replace('---school---', $degree->university, $temp);
  $temp = str_replace('---country---', $degree->country, $temp);
  $temp = str_replace('---city---', $degree->city, $temp);
  echo $temp;
  
  $courses = $degree->courses;
  foreach($courses as $course){
    $temp = str_replace('---name---', $course->name, $fragments[13]);
    $temp = str_replace('---credits---', $course->credits, $temp);
    $temp = str_replace('---university---', $course->university, $temp);
    $temp = str_replace('---DEGREE-ID---', $degree->ID, $temp);
    echo str_replace('---ID---', $course->ID, $temp);
  }
  echo $fragments[14];

}
echo $fragments[15];

if($sessionID != false){
  if($currentPageID == $sessionID){
    echo $fragments[16];
  }
}

echo $fragments[17];

$index = 1;
foreach($comments as $comment){
  $temp = str_replace('---ID---', $comment->ID, $fragments[18]);
  $temp = str_replace('---number---', $index, $temp);
  $temp = str_replace('---SRC---', 'data:image/jpeg;base64,' . base64_encode($comment->image), $temp);
  $temp = str_replace('---DISPLAY_NAME---', $comment->display_name, $temp);
  $temp = str_replace('---text---', $comment->text, $temp);
  $temp = str_replace('---added---', $comment->added, $temp);
  echo $temp;
  if(Session::isLoggedIn()){
    if($currentPageID == $sessionID || $comment->author == $sessionID){
      echo str_replace('---ID---', $comment->ID, $fragments[19]);
    }
  }
  echo $fragments[20];

  $index++;
}
echo $fragments[21];
