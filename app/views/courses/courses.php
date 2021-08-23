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
$temp = str_replace('---country---', $course->country, $temp);
$temp = str_replace('---city---', $course->city, $temp);
$temp = str_replace('---university---', $course->university, $temp);
echo $temp;
echo $fragments[2];

$temp = str_replace('---score---', $score, $fragments[3]);
$temp = str_replace('---total_votes---', $total_votes, $temp);
$temp = str_replace('---popularity-rank---', $POPULARITY_RANK, $temp);
$temp = str_replace('---ranking-rank---', $RATING_RANK, $temp);
$temp = str_replace(' ---reviews--- ', $amountOfReviews, $temp);
echo $temp;
echo $fragments[4];

if(Session::isLoggedIn()){
  $temp = str_replace('---rating---', $rating, $fragments[5]);
  echo str_replace('---ID---', $course->ID, $temp);
}

echo str_replace('---text---', $course->description, $fragments[6]);

if(Session::isLoggedIn()){
  echo str_replace('---ID---', $course->ID, $fragments[7]);
}

echo $fragments[8];

foreach($reviews as $review){
  $temp = str_replace('---userImage---', 'data:image/jpeg;base64,'. base64_encode($review["userImage"]), $fragments[9]);
  $temp = str_replace('---courseID---', $review["courseID"], $temp);
  echo str_replace('---userID---', $review["userID"], $temp);

  if(Session::isLoggedIn() && $review["userID"] == Session::get(SESSION_USERID)){
   $temp = str_replace('---overall---', $review["overall"], $fragments[10]);
   $temp = str_replace('---courseID---', $review["courseID"], $temp);
   echo str_replace('---userID---', $review["userID"], $temp);
  }

  $temp = str_replace('---userDisplayName---', $review["userDisplayName"], $fragments[11]);
  $temp = str_replace('---text---', $review["text"], $temp);
  $temp = str_replace('---fulfilling---', $review["fulfilling"], $temp);
  $temp = str_replace('---environment---', $review["environment"], $temp);
  $temp = str_replace('---difficulty---', $review["difficulty"], $temp);
  $temp = str_replace('---grading---', $review["grading"], $temp);
  $temp = str_replace('---litterature---', $review["litterature"], $temp);
  $temp = str_replace('---overall---', $review["overall"], $temp);
  echo $temp;
}

echo $fragments[12];
