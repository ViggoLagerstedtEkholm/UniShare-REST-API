<?php
require_once 'helper_functions-inc.php';
require_once 'database-inc.php';
session_start();

$database = new Database('localhost', 'root', '', '9.0');
$MAXID = GetMaxID($database->getConn());

if(isset($_SESSION["userID"])){
  for($i = 0; $i <= $MAXID; $i++){
      if(isset($_POST[$i])){
          //Delete the feed if it matches the ID of the clicked feed.
          DeleteProject($database->getConn(), $i, $_SESSION["userID"]);
      }
  }
}else{
  header("location: ../login.php");
  exit();
}
