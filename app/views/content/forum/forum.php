<?php
$html = file_get_contents('app/views/content/forum/forum.html');
$fragments = explode("<!--===edit===-->", $html);

/*
  Render filtering bar and total page-count / page-index / range
*/
$temp = str_replace('---page-count---', $number_of_pages , $fragments[0]);
$temp = str_replace('---page---', $page, $temp);
echo str_replace('---range---', $start_page_first_result + 1 . " - " . $start_page_first_result + $results_per_page , $temp);

/*
  Render all user divs.
*/
foreach($forums as $forum){
  $temp = str_replace('---title---', $forum->title , $fragments[1]);
  $temp = str_replace('---topic---', $forum->topic , $temp);
  $temp = str_replace('---views---', $forum->views, $temp);

  echo str_replace('---SRC---', '/UniShare/images/user.png', $temp);
}

/*
  Render pagination
*/

echo $fragments[2];

if($page != 1 && $number_of_pages != 0){
  $temp = str_replace('---page---', $page - 1, $fragments[3]);
  $temp = str_replace('---filter_option---', $filterOption, $temp);
  $temp = str_replace('---action---', $filterOrder, $temp);
  $temp = str_replace('---search---', $search, $temp);
  $temp = str_replace('---results_per_page_count---', $results_per_page_count, $temp);

  echo $temp;
}

if($page != $number_of_pages && $number_of_pages != 0){
  $temp = str_replace('---page---', $page + 1, $fragments[4]);
  $temp = str_replace('---filter_option---', $filterOption, $temp);
  $temp = str_replace('---action---', $filterOrder, $temp);
  $temp = str_replace('---search---', $search, $temp);
  $temp = str_replace('---results_per_page_count---', $results_per_page_count, $temp);
  echo $temp;
}

$temp = str_replace('---page---', $page, $fragments[5]);
$temp = str_replace('---filter_option---', $filterOption, $temp);
$temp = str_replace('---action---', $filterOrder, $temp);
$temp = str_replace('---search---', $search, $temp);
$temp = str_replace('---results_per_page_count---', $results_per_page_count, $temp);
echo $temp;
