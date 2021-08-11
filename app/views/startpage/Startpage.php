<?php
$html = file_get_contents('app/views/startpage/startpage.html');
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
  $temp = str_replace('---first_name---', $user->getFirst_name() , $fragments[1]);
  $temp = str_replace('---last_name---', $user->getLast_name() , $temp);
  $temp = str_replace('---email---', $user->getEmail() , $temp);
  $temp = str_replace('---last_online---', empty($user->getLastOnline()) ? "None" : $user->getLastOnline(), $temp);

  if($user->getImage() == ""){
    $temp = str_replace('---SRC---', 'images/user.png', $temp);
  }else{
    $temp = str_replace('---SRC---', 'data:image/jpeg;base64,'.$user->getImage(), $temp);
  }

  $temp = str_replace('---visits---', $user->getVisitors(), $temp);
  $temp = str_replace('---courses---', 0, $temp);
  $temp = str_replace('---ID---', $user->getID() , $temp);

  echo $temp;
}

/*
  Render pagination
*/

$temp = str_replace('---page---', $page, $fragments[2]);
$temp = str_replace('---filter_option---', $filterOption, $temp);
$temp = str_replace('---action---', $filterOrder, $temp);

echo $temp;

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

echo $fragments[5];
