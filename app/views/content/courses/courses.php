<?php
use App\Core\Session;
$html = file_get_contents('app/views/content/courses/courses.html');
$fragments = explode("<!--===edit===-->", $html);
//print_r($fragments);
echo $fragments[0];

$temp = str_replace('---page-count---', $number_of_pages , $fragments[1]);
$temp = str_replace('---page---', $page , $temp);
echo str_replace('---range---', $start_page_first_result + 1 . " - " . $start_page_first_result + $results_per_page , $temp);

foreach($courses as $course){
  $temp = str_replace('---name---', $course->name , $fragments[2]);
  $temp = str_replace('---credits---', $course->credits , $temp);
  $temp = str_replace('---duration---', $course->duration , $temp);
  $temp = str_replace('---location---', $course->location , $temp);
  $temp = str_replace('---score---', 0.0, $temp);
  $temp = str_replace('---added---', $course->added , $temp);
  $temp = str_replace('---code---', 0.0, $temp);
  echo $temp;

  if(Session::isLoggedIn()){
    echo $fragments[3];
    echo $fragments[4];
    echo $fragments[5];
  }

 echo str_replace('---ID---', $course->ID , $fragments[6]);
}

echo $fragments[7];
echo $fragments[8];
echo $fragments[9];
echo $fragments[10];
