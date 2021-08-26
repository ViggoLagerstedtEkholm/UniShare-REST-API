<?php
$html = file_get_contents('app/views/admin/admin.html');
$fragments = explode("<!--===edit===-->", $html);
//print_r($fragments);

echo $fragments[0];
foreach($requests as $request){
  $temp = str_replace('---name---', $request["name"], $fragments[1]);
  $temp = str_replace('---ID---', $request["requestID"], $temp);
  $temp = str_replace('---credits---', $request["credits"], $temp);
  $temp = str_replace('---duration---', $request["duration"], $temp);
  $temp = str_replace('---university---', $request["university"], $temp);
  $temp = str_replace('---country---', $request["country"], $temp);
  $temp = str_replace('---city---', $request["city"], $temp);
  echo $temp;
}
echo $fragments[2];
