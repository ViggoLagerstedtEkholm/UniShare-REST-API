<?php
session_start();
?>

<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Title</title>
    <meta name="description" content="A simple HTML5 Template for new projects.">
    <link rel="stylesheet" href="/UniShare/css/header.css">
  </head>
  <body>
    <nav>
      <div class="logo">
        <h4>UniShare</h4>
      </div>
      <ul class="nav-links">
        <?php
          if(isset($_SESSION["userID"])){
            $Email = $_SESSION["userEmail"];
            $Id = $_SESSION["userID"];
            echo "<li><a>$Email</a></li>";
            echo "<li><a>$Id</a></li>";
            echo "<li><a href='profile.php?ID=$Id'>Profile</a></li>";
            echo "<li><a href='classes\includes\logout-inc.php'>Logout</a></li>";
          }else{
            echo "<li><a href='login.php'>Login</a></li>";
            echo "<li><a href='register.php'>Register</a></li>";
          }
        ?>
        <li><a href="startpage.php">Home</a></li>
      </ul>
    </nav>
  </body>
</html>
