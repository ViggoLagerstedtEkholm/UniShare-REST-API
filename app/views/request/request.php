<?php
$html = file_get_contents('app/views/request/request.html');
$fragments = explode("<!--===edit===-->", $html);
//print_r($fragments);

echo $fragments[0];

foreach($requests as $request){
  $temp = str_replace('---ID---', $request->ID, $fragments[1]);
  $temp = str_replace('---name---', $request->name, $temp);
  $temp = str_replace('---credits---', $request->credits, $temp);
  $temp = str_replace('---duration---', $request->duration, $temp);
  $temp = str_replace('---university---', $request->university, $temp);
  $temp = str_replace('---country---', $request->country, $temp);
  $temp = str_replace('---city---', $request->city, $temp);
  echo $temp;

  echo str_replace('---ID---', $request->ID, $fragments[2]);

  echo $fragments[3];
}

echo $fragments[4];
