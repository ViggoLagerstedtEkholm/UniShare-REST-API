<?php
$html = file_get_contents('app/views/startpage/startpage.html');
$fragments = explode("<!--===edit===-->", $html);

echo $fragments[0];

if(!is_null($currentUser)){
  $temp = str_replace('---first_name---', $currentUser['userFirstName'], $fragments[1]);
  if($currentUser['userImage'] == ''){
    $temp = str_replace('---SRC---', 'images/user.png', $temp);
  }else{
    $temp = str_replace('---SRC---', 'data:image/jpeg;base64,'.base64_encode($currentUser['userImage']), $temp);
  }
  $temp = str_replace('---last_name---', $currentUser['userLastName'], $temp);
  $temp = str_replace('---email---', $currentUser['userEmail'], $temp);
  $temp = str_replace('---visits---', $currentUser['visits'], $temp);
  $temp = str_replace('---projects---', 0, $temp);
  $temp = str_replace('---courses---', 0, $temp);
  $temp = str_replace('---last_online---', $currentUser['lastOnline'], $temp);
  $temp = str_replace('---ID---', $currentUser['usersID'], $temp);
  echo $temp;
}
echo $fragments[2];

foreach($courses as $item){
  $temp = str_replace('---ID---', $item->ID, $fragments[3]);
  $temp = str_replace('---course_name---', $item->name, $temp);
  $temp = str_replace('---score---', 0.0, $temp);
  $temp = str_replace('---total-votes---', 0.0, $temp);
  echo $temp;
}

echo $fragments[4];
