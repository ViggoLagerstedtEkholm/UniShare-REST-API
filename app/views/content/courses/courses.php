<?php
use App\Core\Session;
$html = file_get_contents('app/views/content/courses/courses.html');
$fragments = explode("<!--===edit===-->", $html);
//print_r($fragments);
/*
  Render filtering bar and total page-count / page-index / range
*/
$temp = str_replace('---page-count---', $number_of_pages , $fragments[0]);
$temp = str_replace('---page---', $page , $temp);
echo str_replace('---range---', $start_page_first_result + 1 . " - " . $start_page_first_result + $results_per_page , $temp);

/*
  Render all user divs.
*/
foreach($courses as $course){
  $temp = str_replace('---name---', $course->name , $fragments[1]);
  $temp = str_replace('---credits---', $course->credits , $temp);
  $temp = str_replace('---duration---', $course->duration, $temp);
  $temp = str_replace('---score---', $course->rating, $temp);
  $temp = str_replace('---university---', $course->university, $temp);
  $temp = str_replace('---country---', $course->country, $temp);
  $temp = str_replace('---city---', $course->city, $temp);
  $temp = str_replace('---added---', $course->added, $temp);
  $temp = str_replace('---score---', 0.0, $temp);
  echo str_replace('---courses---', 0, $temp);

  if(Session::isLoggedIn()){
    if($course->existsInActiveDegree){
      $temp = str_replace('---ADD_REMOVE---', "REMOVE from degree", $fragments[2]);
      $temp = str_replace('---CSS---', "button-style-2", $temp);

      echo str_replace('---ID---', $course->ID, $temp);
    }else{
      $temp = str_replace('---ADD_REMOVE---', "ADD to degree", $fragments[2]);
      $temp = str_replace('---CSS---', "button-style-3", $temp);

      echo str_replace('---ID---', $course->ID, $temp);
    }
  }

  echo str_replace('---ID---', $course->ID, $fragments[3]);
}

echo $fragments[4];

if($page != 1){
  $temp = str_replace('---page---', $page - 1, $fragments[5]);
  $temp = str_replace('---filter_option---', $filterOption, $temp);
  $temp = str_replace('---action---', $filterOrder, $temp);
  $temp = str_replace('---search---', $search, $temp);
  echo $temp;
}

if($page != $number_of_pages){
  $temp = str_replace('---page---', $page + 1, $fragments[6]);
  $temp = str_replace('---filter_option---', $filterOption, $temp);
  $temp = str_replace('---action---', $filterOrder, $temp);
  $temp = str_replace('---search---', $search, $temp);
  echo $temp;
}

$temp = str_replace('---page---', $page, $fragments[7]);
$temp = str_replace('---filter_option---', $filterOption, $temp);
$temp = str_replace('---action---', $filterOrder, $temp);
$temp = str_replace('---search---', $search, $temp);
echo $temp;
