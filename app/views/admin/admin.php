<?php
$html = file_get_contents('app/views/admin/admin.html');
$fragments = explode("<!--===edit===-->", $html);

$temp = str_replace('---page-count---', $number_of_pages , $fragments[0]);
$temp = str_replace('---page---', $page , $temp);
echo str_replace('---range---', $start_page_first_result + 1 . " - " . $start_page_first_result + $results_per_page , $temp);

if(count($courses) > 0){
  foreach($courses as $item){
    $temp = str_replace('---SRC---', "images/books.png", $fragments[1]);
    $temp = str_replace('---name---', $item->name, $temp);
    $temp = str_replace('---credits---', $item->credits, $temp);
    $temp = str_replace('---duration---', $item->duration, $temp);
    $temp = str_replace('---added---', $item->added, $temp);
    $temp = str_replace('---field---', $item->fieldOfStudy, $temp);
    echo $temp;
  }
}else{
  echo "No existing courses";
}
echo $fragments[2];
echo $fragments[3];
echo $fragments[4];
echo $fragments[5];
