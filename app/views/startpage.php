<?php
include_once 'header.php';
require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/UniShare/vendor/autoload.php");
use \App\Controllers;

$html = file_get_contents("html/startpage.html");

$usersController = new Controllers\UsersController();
$projectsController = new Controllers\ProjectsController();

$usersController->connect_database();
$projectsController->connect_database();

$filterOption = "none";
$filterOrder = "DESC";
if(isset($_GET["filter_option"])){
  $filterOption = $_GET['filter_option'];
  if(isset($_GET['action'])){
    $filterOrder = $_GET["action"];
  }
}

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

$user_count = $usersController->get_userCount()["COUNT(*)"];
$fragments = getPieces($html, "<!--===edit===-->");

$results_per_page = 6;
$number_of_pages = ceil($user_count / $results_per_page);
$start_page_first_result = ($page-1) * $results_per_page;
$users = $usersController->get_ShowcaseUsersPage($start_page_first_result, $results_per_page, $filterOption, $filterOrder);

$temp = str_replace('---page-count---', $number_of_pages , $fragments[0]);
$temp = str_replace('---page---', $page , $temp);
echo str_replace('---range---', $start_page_first_result + 1 . " - " . $start_page_first_result + $results_per_page , $temp);

foreach($users as $user){
  $projects = $projectsController->get_Projects($user->getID());

  $temp = str_replace('---first_name---', $user->getFirst_name() , $fragments[1]);
  $temp = str_replace('---last_name---', $user->getLast_name() , $temp);
  $temp = str_replace('---email---', $user->getEmail() , $temp);
  $temp = str_replace('---last_online---', empty($user->getLastOnline()) ? "None" : $user->getLastOnline(), $temp);

  if($user->getImage() == ""){
    $temp = str_replace('---SRC---', '../../images/user.png', $temp);
  }else{
    $temp = str_replace('---SRC---', 'data:image/jpeg;base64,'.$user->getImage(), $temp);
  }

  $temp = str_replace('---visits---', $user->getVisitors(), $temp);
  $temp = str_replace('---projects---', count($projects), $temp);
  $temp = str_replace('---courses---', 0, $temp);
  $temp = str_replace('---ID---', $user->getID() , $temp);

  echo $temp;
}

echo $fragments[2];

if($page != 1){
  $temp = str_replace('---page---', $page - 1, $fragments[3]);
  $temp = str_replace('---filter_option---', $filterOption, $temp);
  $temp = str_replace('---action---', $filterOrder, $temp);
  echo $temp;
}

if($page != $number_of_pages){
  $temp = str_replace('---page---', $page + 1, $fragments[4]);
  $temp = str_replace('---filter_option---', $filterOption, $temp);
  $temp = str_replace('---action---', $filterOrder, $temp);
  echo $temp;
}

function getPieces($html){
  //Get the segments from the html file we want to modify.
  return explode("<!--===edit===-->", $html);
}

$projectsController->close_database();
$usersController->close_database();
