<?php
use App\Core\Session;

if($isError == 0){
  $html = file_get_contents('app/views/layout/header.html');
  $fragments = explode("<!--admin-->", $html);
  if(Session::exists(SESSION_USERID)){
    $ID = Session::get('userID');
    $headerLinks = "<li><a href='./'>Home</a></li>
                    <li><a href='./profile?ID=$ID'>Profile</a></li>
                    <li><a href='./logout'>Logout</a></li>";

    echo str_replace('---navigation---', $headerLinks, $fragments[0]);
  }else{
    $headerLinks = "<li><a href='./'>Home</a></li>
                    <li><a href='./login'>Login</a></li>
                    <li><a href='./register'>Register</a></li>";

    echo str_replace('---navigation---', $headerLinks, $fragments[0]);
  }
  
  if(Session::exists(SESSION_PRIVILEGE)){
    $privilege = Session::get(SESSION_PRIVILEGE);
    if($privilege == ADMIN){
      echo $fragments[1];
    }
  }

  echo $fragments[2];
}
