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
      <h1>Register</h1>
      <form action="includes/register-inc.php" method="post">
        <div class="text_field">
          <input type="text" name="first_name" required>
          <label>First name</label>
        </div>
        <div class="text_field">
          <input type="text" name="last_name" required>
          <label>Last name</label>
        </div>
        <div class="text_field">
          <input type="text" name="email" required>
          <label>Email</label>
        </div>
        <div class="text_field">
          <input type="text" name="username" required>
          <label>Display name</label>
        </div>
        <div class="text_field">
          <input type="password" name="password" required>
          <label>Password</label>
        </div>
        <div class="text_field">
          <input type="password" name="password_repeat" required>
          <label>Repeat password</label>
        </div>
        <input type="submit" name="submit_register" value="Register">
      </form>
      <h4 class="form-authentication-text">Already have an account? <a href="login.php">Login</a></h4>
    </div>

    <script>
    var result = findGetParameter("error");

    var username = getElement("username");
    var email = getElement("email");
    var password_repeat = getElement("password_repeat");
    var password = getElement("password");
    var first_name = getElement("first_name");
    var last_name = getElement("last_name");

    document.getElementsByName("first_name")[0].addEventListener("keydown", function(){
      first_name.setAttribute("style", "border: none");
    });

    document.getElementsByName("last_name")[0].addEventListener("keydown", function(){
      last_name.setAttribute("style", "border: none");
    });

    document.getElementsByName("email")[0].addEventListener("keydown", function(){
      email.setAttribute("style", "border: none");
    });

    document.getElementsByName("username")[0].addEventListener("keydown", function(){
      username.setAttribute("style", "border: none");
    });

    document.getElementsByName("password_repeat")[0].addEventListener("keydown", function(){
      password_repeat.setAttribute("style", "border: none");
    });

    if(result){

      switch(result){
        case "emptyinput":
        alert("Please enter all fields!");
        username.setAttribute("style", "border-bottom: 2px solid red; ");
        email.setAttribute("style", "border-bottom: 2px solid red; ");
        password_repeat.setAttribute("style", "border-bottom: 2px solid red; ");
        password.setAttribute("style", "border-bottom: 2px solid red; ");
        first_name.setAttribute("style", "border-bottom: 2px solid red; ");
        last_name.setAttribute("style", "border-bottom: 2px solid red; ");
        break;
        case "invalidUsername":
        alert("Failed to register! Username is invalid!");
        username.setAttribute("style", "border-bottom: 2px solid red; ");
        break;
        case "invalidEmail":
        alert("Failed to register! Email is invalid!");
        email.setAttribute("style", "border-bottom: 2px solid red; ");
        break;
        case "invalidpasswordrepeat":
        alert("Failed to register! Password does not match!");
        password_repeat.setAttribute("style", "border-bottom: 2px solid red; ");
        break;
        case "usernameoremailtaken":
        alert("Failed to register! Email or username already taken!");
        username.setAttribute("style", "border-bottom: 2px solid red; ");
        email.setAttribute("style", "border-bottom: 2px solid red; ");
        break;
        case "none":
        alert("Created account!");
        window.location.href = './login.php';
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
  </body>
</html>
