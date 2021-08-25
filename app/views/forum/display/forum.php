<?php
use App\Core\Session;

$html = file_get_contents('app/views/forum/display/forum.html');
$fragments = explode("<!--===edit===-->", $html);
//print_r($fragments);

$temp = str_replace('---title---', $forum["title"], $fragments[0]);
$temp = str_replace('---created---', $forum["created"], $temp);
$temp = str_replace('---views---', $forum["views"], $temp);
$temp = str_replace('---page-count---', $number_of_pages , $temp);
$temp = str_replace('---page---', $page , $temp);
$temp = str_replace('---ID---', $forum["forumID"] , $temp);
echo str_replace('---range---', $start_page_first_result + 1 . " - " . $start_page_first_result + $results_per_page , $temp);

foreach($posts as $post){
  if($post["userID"] == $forum["creator"]){
    echo "Thread starter";
  }
  $temp = str_replace('---userImage---', 'data:image/jpeg;base64,'.base64_encode($post["userImage"]), $fragments[1]);
  $temp = str_replace('---userDisplayName---', $post["userDisplayName"], $temp);
  $temp = str_replace('---posted---', $post["date"], $temp);
  echo str_replace('---text---', $post["text"], $temp);
}

echo $fragments[2];

if($page != 1 && $number_of_pages != 0){
  $temp = str_replace('---page---', $page - 1, $fragments[3]);
  echo str_replace('---ID---', $forum["forumID"], $temp);
}

if($page != $number_of_pages && $number_of_pages != 0){
  $temp =  str_replace('---page---', $page + 1, $fragments[4]);
  echo str_replace('---ID---', $forum["forumID"], $temp);
}

$temp = str_replace('---page---', $page, $fragments[5]);
echo str_replace('---ID---', $forum["forumID"], $temp);
