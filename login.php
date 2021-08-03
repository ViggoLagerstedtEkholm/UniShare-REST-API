<?php
include_once 'header.php';
?>

<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Title</title>
    <meta name="description" content="A simple HTML5 Template for new projects.">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/header.css">
  </head>
  <body>
    <div class="form-authentication">
      <h1>Login</h1>
      <form action="includes/login-inc.php" method="post">
        <div class="text_field">
          <input type="text" name="email" required>
          <label>Email</label>
        </div>
        <div class="text_field">
          <input type="password" name="password" required>
          <label>Password</label>
        </div>
        <input type="submit" name="submit_login" value="Login">
      </form>
      <h4 class="form-authentication-text">Do not have an account? <a href="register.php">Register</a></h4>
    </div>
  </body>

  <script>
  var result = findGetParameter("error");

  var email = getElement("email");
  var password = getElement("password");

  document.getElementsByName("email")[0].addEventListener("keydown", function(){
    email.setAttribute("style", "border: none");
  });

  document.getElementsByName("password")[0].addEventListener("keydown", function(){
    password.setAttribute("style", "border: none");
  });

  if(result){
    switch(result){
      case "emptyinput":
      alert("Please enter all fields!");
      email.setAttribute("style", "border-bottom: 2px solid red; ");
      password.setAttribute("style", "border-bottom: 2px solid red; ");
      break;
      case "wrongemailorpassword":
      alert("Wrong email or password.");
      email.setAttribute("style", "border-bottom: 2px solid red; ");
      password.setAttribute("style", "border-bottom: 2px solid red; ");
      break;
    }
  }

  function getElement(name){
    return document.getElementsByName(name)[0];
  }

  function findGetParameter(parameterName) {
      var result = null,
          tmp = [];
      var items = location.search.substr(1).split("&");
      for (var index = 0; index < items.length; index++) {
          tmp = items[index].split("=");
          if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
      }
      return result;
  }
  </script>
</html>