<?php
$html = file_get_contents('app/views/content/people/people.html');
$fragments = explode("<!--===edit===-->", $html);

/*
  Render filtering bar and total page-count / page-index / range
*/
$temp = str_replace('---page-count---', $number_of_pages , $fragments[0]);
$temp = str_replace('---page---', $page , $temp);
echo str_replace('---range---', $start_page_first_result + 1 . " - " . $start_page_first_result + $results_per_page , $temp);

/*
  Render all user divs.
*/
foreach($users as $user){
  $temp = str_replace('---first_name---', $user->first_name , $fragments[1]);
  $temp = str_replace('---last_name---', $user->last_name , $temp);
  $temp = str_replace('---email---', $user->email, $temp);
  $temp = str_replace('---username---', $user->display_name , $temp);
  $temp = str_replace('---last_online---', empty($user->last_online) ? "None" : $user->last_online, $temp);

  if($user->image == ""){
    $temp = str_replace('---SRC---', 'images/user.png', $temp);
  }else{
    $temp = str_replace('---SRC---', 'data:image/jpeg;base64,'.$user->image, $temp);
  }

  $temp = str_replace('---visits---', $user->visitors, $temp);
  $temp = str_replace('---courses---', 0, $temp);
  $temp = str_replace('---ID---', $user->ID, $temp);

  echo $temp;
}

/*
  Render pagination
*/

echo $fragments[2];

if($page != 1){
  $temp = str_replace('---page---', $page - 1, $fragments[3]);
  $temp = str_replace('---filter_option---', $filterOption, $temp);
  $temp = str_replace('---action---', $filterOrder, $temp);
  $temp = str_replace('---search---', $search, $temp);
  echo $temp;
}

if($page != $number_of_pages){
  $temp = str_replace('---page---', $page + 1, $fragments[4]);
  $temp = str_replace('---filter_option---', $filterOption, $temp);
  $temp = str_replace('---action---', $filterOrder, $temp);
  $temp = str_replace('---search---', $search, $temp);
  echo $temp;
}

$temp = str_replace('---page---', $page, $fragments[5]);
$temp = str_replace('---filter_option---', $filterOption, $temp);
$temp = str_replace('---action---', $filterOrder, $temp);
$temp = str_replace('---search---', $search, $temp);
echo $temp;
