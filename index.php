<?php
include_once 'header.php';
require_once 'includes/helper_functions-inc.php';
require_once 'includes/database-inc.php';
require_once 'includes/user-inc.php';

$html = file_get_contents("html/startpage.html");

$database = new Database('localhost', 'root', '', '9.0');
$fragments = getPieces($html, "<!--===edit===-->");
//print_r($fragments);
$user_count = getUserCount($database->getConn())["COUNT(*)"];
$results_per_page = 6;
$number_of_pages = ceil($user_count / $results_per_page);

if(!isset($_GET['page'])){
  $page = 1;
}else{
  $page = $_GET['page'];
}

if(isset($_GET["page_number"])){
  if($_GET["page_number"] <= $number_of_pages && $_GET["page_number"] > 0){
    $page = $_GET["page_number"];
  }else{
    $page = 1;
  }
}

$start_page_first_result = ($page-1) * $results_per_page;
$users = getShowcaseUsersPage($database->getConn(), $start_page_first_result, $results_per_page);

echo str_replace('---page-count---', $number_of_pages , $fragments[0]);

foreach($users as $user){
  $temp = str_replace('---ID---', $user->getID() , $fragments[1]);
  if($user->getImage() == ""){
    $temp = str_replace('---SRC---', 'images/user.png', $temp);
  }else{
    $temp = str_replace('---SRC---', 'data:image/jpeg;base64,'.$user->getImage(), $temp);
  }
  $temp = str_replace('---first_name---', $user->getFirst_name() , $temp);
  $temp = str_replace('---last_name---', $user->getLast_name() , $temp);
  $temp = str_replace('---email---', $user->getEmail() , $temp);
  $temp = str_replace('---last_online---', $user->getLastOnline() , $temp);

  $temp = str_replace('---visits---', $user->getVisitors(), $temp);
  $temp = str_replace('---projects---', 0, $temp);
  $temp = str_replace('---courses---', 0, $temp);
  echo $temp;
}

echo str_replace('---page---', $page , $fragments[2]);

if($page != 1){
  echo str_replace('---page---', $page - 1, $fragments[3]);
}

if($page != $number_of_pages){
  echo str_replace('---page---', $page + 1, $fragments[4]);
}

echo $fragments[5];


function getPieces($html){
  //Get the segments from the html file we want to modify.
  return explode("<!--===edit===-->", $html);
}
