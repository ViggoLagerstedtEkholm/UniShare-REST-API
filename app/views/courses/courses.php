<?php
use App\Core\Session;
$html = file_get_contents('app/views/courses/courses.html');
$fragments = explode("<!--===edit===-->", $html);
//print_r($fragments);
echo $fragments[0];

$temp = str_replace('---name---', $course->name, $fragments[1]);
$temp = str_replace('---credits---', $course->credits, $temp);
$temp = str_replace('---duration---', $course->duration, $temp);
$temp = str_replace('---added---', $course->added, $temp);
$temp = str_replace('---field---', $course->field_of_study, $temp);
$temp = str_replace('---location---', "TODO", $temp);
$temp = str_replace('---university---', "TODO", $temp);
echo $temp;
echo $fragments[2];

$temp = str_replace('---score---', $score, $fragments[3]);
$temp = str_replace('---total_votes---', $total_votes, $temp);
$temp = str_replace('---popularity-rank---', $POPULARITY_RANK, $temp);
$temp = str_replace('---ranking-rank---', $RATING_RANK, $temp);
echo $temp;
echo $fragments[4];

if(Session::isLoggedIn()){
  echo str_replace('---rating---', $rating, $fragments[5]);
}

echo $fragments[6];

if(Session::isLoggedIn()){
  echo str_replace('---ID---', $course->ID, $fragments[7]);
}

echo $fragments[8];