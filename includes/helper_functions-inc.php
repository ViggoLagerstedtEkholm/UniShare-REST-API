<?php
require_once 'project-inc.php';

function hasEmptyInputsRegister($user){
  $result;
  if(empty($user->getFirst_name()) || empty($user->getLast_name())
  || empty($user->getEmail()) || empty($user->getPassword()
  || empty($user->getPassword_repeat()) || empty($user->getDisplay_name()))){
    $result = true;
  }else{
    $result = false;
  }
  return $result;
}

function hasEmptyInputsLogin($user){
  $result;
  if(empty($user->getEmail()) || empty($user->getPassword()))
  {
    $result = true;
  }else{
    $result = false;
  }
  return $result;
}

function invalidUsername($username){
  $result;
  if(!preg_match("/^[a-zA-Z0-9]*$/", $username)){
    $result = true;
  }else{
    $result = false;
  }
  return $result;
}

function invalidEmail($email){
  $result;
  if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    $result = true;
  }else{
    $result = false;
  }
  return $result;
}

function passwordMatch($password, $password_repeat){
  $result;
  if($password !== $password_repeat){
    $result = true;
  }else{
    $result = false;
  }
  return $result;
}

function usernameExists($conn, $display_name, $ID){
  $sql = "SELECT * FROM users WHERE userEmail = ? OR usersID = ?;";
  $stmt = mysqli_stmt_init($conn);

  if(!mysqli_stmt_prepare($stmt, $sql)){
    header("location: ../register.php?error=failedstmt");
    exit();
  }
  mysqli_stmt_bind_param($stmt, "ss", $display_name, $ID);
  mysqli_stmt_execute($stmt);

  $resultData = mysqli_stmt_get_result($stmt);

  if($row = mysqli_fetch_assoc($resultData)){
    return $row;
  }else{
    $result = false;
    return $result;
  }
  mysqli_stmt_close($stmt);
}

function getShowcaseUsersPage($conn, $from, $to, $option, $filterOrder){
  $sql = "";

  switch($option){
    case "none":
    $sql = "SELECT usersID, userFirstName, userLastName, userEmail, userDisplayName, userImage, visits, lastOnline FROM users LIMIT ?, ?;";
    break;
    case "visits":
      switch($filterOrder){
        case "DESC":
        $sql = "SELECT usersID, userFirstName, userLastName, userEmail, userDisplayName, userImage, visits, lastOnline FROM users ORDER BY visits DESC LIMIT ?, ?; ";
        break;
        case "ASC":
        $sql = "SELECT usersID, userFirstName, userLastName, userEmail, userDisplayName, userImage, visits, lastOnline FROM users ORDER BY visits ASC LIMIT ?, ?; ";
        break;
      }
    break;
    case "last_online":
      switch($filterOrder){
        case "DESC":
        $sql = "SELECT usersID, userFirstName, userLastName, userEmail, userDisplayName, userImage, visits, lastOnline FROM users ORDER BY lastOnline DESC LIMIT ?, ?; ";
        break;
        case "ASC":
        $sql = "SELECT usersID, userFirstName, userLastName, userEmail, userDisplayName, userImage, visits, lastOnline FROM users ORDER BY lastOnline ASC LIMIT ?, ?; ";
        break;
      }
    break;
    default:
    header("location: ../index.php?error=failedorderquery");
  }

  $stmt = mysqli_stmt_init($conn);

  if(!mysqli_stmt_prepare($stmt, $sql)){
    echo $sql;
    //header("location: ../index.php?error=failedpaginationquery");
    //exit();
  }
  mysqli_stmt_bind_param($stmt, "ss", $from, $to);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  $users = array();
  while( $row = $result->fetch_array() )
  {
      $ID = $row['usersID'];
      $first_name = $row['userFirstName'];
      $last_name = $row['userLastName'];
      $email =  $row['userEmail'];
      $display_name = $row['userDisplayName'];
      $image = base64_encode($row['userImage']);
      $last_online = $row['lastOnline'];
      $visitors = $row['visits'];

      $user = new User($first_name, $last_name, $email, $display_name);
      $user->setImage($image);
      $user->setID($ID);
      $user->setLastOnline($last_online);
      $user->setVistiors($visitors);
      $users[] = $user;
  }
  mysqli_stmt_close($stmt);

  return $users;
}

function addVisitor($conn, $ID){
  $usernameExists = usernameExists($conn, $ID, $ID);
  $visits = $usernameExists["visits"];

  $updatedVisits = $visits + 1;

  $sql = "UPDATE users SET visits =? WHERE usersID = ?;";

  $stmt = mysqli_stmt_init($conn);

  if(!mysqli_stmt_prepare($stmt, $sql)){
    header("location: ../profile.php?error=visitcounterror");
    exit();
  }

  mysqli_stmt_bind_param($stmt, "ii", $updatedVisits , $ID);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);

  return $updatedVisits;
}

function addVisitDate($conn, $ID){
  $sql = "UPDATE users SET lastOnline =? WHERE usersID = ?;";

  date_default_timezone_set("Europe/Stockholm");
  $Date = date("Y-m-d",time());

  $stmt = mysqli_stmt_init($conn);

  if(!mysqli_stmt_prepare($stmt, $sql)){
    header("location: ../profile.php?error=dateinsertfail");
    exit();
  }

  mysqli_stmt_bind_param($stmt, "si", $Date, $ID);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);

  return $Date;
}

function createUser($conn, $user){
  $sql = "INSERT INTO users (userFirstName, userLastName, userEmail, userDisplayName, usersPassword) values(?,?,?,?,?);";
  $stmt = mysqli_stmt_init($conn);

  if(!mysqli_stmt_prepare($stmt, $sql)){
    header("location: ../register.php?error=failedstmt");
    exit();
  }

  $first_name = $user->getFirst_name();
  $last_name = $user->getLast_name();
  $email = $user->getEmail();
  $display_name = $user->getDisplay_name();
  $password = $user->getPassword();

  $hashPassword = password_hash($password, PASSWORD_DEFAULT);

  mysqli_stmt_bind_param($stmt, "sssss", $first_name, $last_name, $email, $display_name, $hashPassword);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);
  header("location: ../register.php?error=none");
  exit();
}

function getImage($conn, $ID){
  $usernameExists = usernameExists($conn, $ID, $ID);

  if($usernameExists === false){
    header("location: ../login.php?error=wrongemailorpassword");
    exit();
  }

  $image = $usernameExists["userImage"];
  return base64_encode($image);
}

function uploadImage($conn, $image, $ID){
  $sql = "UPDATE users SET userImage =? WHERE usersID = ?;";
  $stmt = mysqli_stmt_init($conn);

  if(!mysqli_stmt_prepare($stmt, $sql)){
    header("location: ../profile.php?error=uploadfail");
    exit();
  }

  mysqli_stmt_bind_param($stmt, "si", $image, $ID);
  mysqli_stmt_execute($stmt);
  if (!mysqli_stmt_execute($stmt))
  {
      echo "Failed to insert image! " . $stmt->error;
  }
  mysqli_stmt_close($stmt);

  header("location: ../profile.php?ID=$ID");
  exit();
}

function DeleteProject($conn, $ID, $currentID){
  $sql = "DELETE FROM projects WHERE projectID = ?;";
  $stmt = mysqli_stmt_init($conn);

  if(!mysqli_stmt_prepare($stmt, $sql)){
    header("location: ../profile.php?ID=$currentID");
    exit();
  }

  mysqli_stmt_bind_param($stmt, "s", $ID);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);
  header("location: ../profile.php?ID=$currentID");
  exit();
}

function GetMaxID($conn){
    if($conn->connect_error){
      die('Connection Failed: ' . $conn->connect_error);
    }else{
      $changedate = "";
        $sql = "SELECT MAX(projectID) FROM projects";
        $result = $conn->query($sql)->fetch_assoc();
        return $result;
    }
    return 0;
}

function getUserCount($conn){
    if($conn->connect_error){
      die('Connection Failed: ' . $conn->connect_error);
    }else{
      $changedate = "";
        $sql = "SELECT COUNT(*) FROM users";
        $result = $conn->query($sql)->fetch_assoc();
        return $result;
    }
    return 0;
  }

function getProjects($conn, $ID){
  $projects = array();
  if($conn->connect_error){
    die('Connection Failed: ' . $this->conn->connect_error);
  }else{
     $sql = "SELECT * FROM projects WHERE userID=?;";
     $stmt = $conn->prepare($sql);
     $stmt->bind_param("i", $ID);
     $stmt->execute();
     $result = $stmt->get_result(); // get the mysqli result

     while ($row = $result->fetch_assoc())
     {
          $ID = $row["projectID"];
          $name = $row["name"];
          $description = $row["description"];
          $link = $row["link"];
          $image = $row["image"];

          $project = new Project($name, $description, $link, base64_encode($image));
          $project->setID($ID);
          $projects[] = $project;
      }
  }
  return $projects;
}

function uploadProject($conn, $project, $ID){
  $sql = "INSERT INTO projects (name, description, link, userID, image) values (?,?,?,?,?);";
  $stmt = mysqli_stmt_init($conn);

  if(!mysqli_stmt_prepare($stmt, $sql)){
    header("location: ../register.php?error=failedstmt");
    exit();
  }

  $name = $project->getName();
  $description = $project->getDescription();
  $link = $project->getLink();
  $image = $project->getImage();

  mysqli_stmt_bind_param($stmt, "sssss", $name, $description, $link, $ID, $image);

  $result = mysqli_stmt_execute($stmt);
  if($result){
    header("location: ../profile.php?ID=$ID");
    exit();
  }else{
    header("location: ../profile.php?ID=$ID&error=queryfail");
    exit();
  }
  mysqli_stmt_close($stmt);
}

function loginUser($conn, $user){
  $usernameExists = usernameExists($conn, $user->getEmail(), $user->getEmail());

  if($usernameExists === false){
    header("location: ../login.php?error=wrongemailorpassword");
    exit();
  }

  $passwordHash = $usernameExists["usersPassword"];
  $comparePassword = password_verify($user->getPassword(), $passwordHash);

  if($comparePassword === false){
    header("location: ../login.php?error=wrongemailorpassword");
    exit();
  }else if($comparePassword === true){
    session_start();
    $_SESSION["userID"] =  $usernameExists["usersID"];
    $_SESSION["userEmail"] =  $usernameExists["userEmail"];
    header("location: ../index.php");
    exit();
  }
}
