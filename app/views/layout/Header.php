<?php
use App\Core\Session;

if($isError == 0){
  $html = file_get_contents('app/views/layout/header.html');
  $fragments = explode("<!--admin-->", $html);

  if(Session::exists('userID')){
    $ID = Session::get('userID');
    $headerLinks = "<li><a href='./'>Home</a></li>
                    <li><a href='./profile?ID=$ID'>Profile</a></li>
                    <li><a href='./logout'>Logout</a></li>";

    $html = str_replace('---navigation---', $headerLinks, $html);
  }else{
    $headerLinks = "<li><a href='./'>Home</a></li>
                    <li><a href='./login'>Login</a></li>
                    <li><a href='./register'>Register</a></li>";

    $html = str_replace('---navigation---', $headerLinks, $html);
  }

  echo $html;
}
