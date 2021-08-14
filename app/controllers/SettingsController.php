<?php
namespace App\Controllers;
use App\Middleware\AuthenticationMiddleware;
use App\Models\MVCModels\Users;
use App\Core\Session;
use App\Core\Request;
use App\Includes\Validate;
use App\Core\Application;

class SettingsController extends Controller{
  function __construct(){
    $this->setMiddlewares(new AuthenticationMiddleware(['view', 'deleteAccount', 'getSettings', 'update']));
    $this->users = new Users();
  }

  public function view(){
    return $this->display('settings', 'settings', []);
  }

  public function update(Request $request){
    $fields = ["userFirstName", "userLastName", "userEmail", "userDisplayName", "usersPassword", "activeDegreeID"];

    $updatedInfo = $request->getBody();

    $updated_first_name = $updatedInfo["first_name"];
    $updated_last_name = $updatedInfo["last_name"];
    $updated_email = $updatedInfo["email"];
    $updated_display_name = $updatedInfo["display_name"];
    $updated_current_password = $updatedInfo["current_password"];
    $updated_new_password = $updatedInfo["new_password"];
    $updated_activeDegreeID = $updatedInfo["activeDegreeID"];

    $user = $this->users->getUser(Session::get(SESSION_USERID));
    $ID = $user["usersID"];
    $first_name = $user["userFirstName"];
    $last_name = $user["userLastName"];
    $email = $user["userEmail"];
    $display_name = $user["userDisplayName"];
    $passwordHash = $user["usersPassword"];
    $activeDegreeID = $user["activeDegreeID"];

    $error = array();

    if(!$this->users->userHasDegreeID($updated_activeDegreeID)){
      $error [] = INVALID_ACTIVEDEGREEID;
    }

    if(!empty($updated_current_password) && !empty($updated_new_password)){
      $comparePassword = password_verify($updated_current_password, $passwordHash);

      if($comparePassword === false){
        $error [] = INVALID_PASSWORD_MATCH;
      }else if($comparePassword === true){
        $hashPassword = password_hash($updated_new_password, PASSWORD_DEFAULT);
        $this->users->updateUser($fields[4], $hashPassword, $ID);
      }
    }

    if(Validate::emptyValue($updated_last_name) === true){
      $error [] = INVALID_LAST_NAME;
    }
    if(Validate::invalidUsername($updated_display_name) === true){
      $error [] = INVALID_USERNAME;
    }
    if(!is_null($this->users->userExists($fields[2], $updated_email)) && $updated_email != $email){
      $error [] = EMAIL_TAKEN;
    }
    if(!is_null($this->users->userExists($fields[3], $updated_email)) && $updated_display_name != $display_name){
      $error [] = INVALID_USERNAME;
    }

    $URL = "";
    $errorCount = count($error);
    if($errorCount > 0){
      for ($x = 0; $x < $errorCount; $x++) {
        $x == $errorCount - 1 ? $URL .= $error[$x] : $URL .= $error[$x] . "&error=";
      }
      Application::$app->redirect("../settings?error=" . $URL);
      exit();
    }
    if($updated_activeDegreeID != $activeDegreeID){$this->users->updateUser($fields[5], $updated_activeDegreeID, $ID);}
    if($updated_first_name != $first_name){$this->users->updateUser($fields[0], $updated_first_name, $ID);}
    if($updated_last_name != $last_name){$this->users->updateUser($fields[1], $updated_last_name, $ID);}
    if($updated_email != $email){$this->users->updateUser($fields[2], $updated_email, $ID);}
    if($updated_display_name != $display_name){$this->users->updateUser($fields[3], $updated_display_name, $ID);}
    Application::$app->redirect("../");
  }

  public function fetch(){
    $user = $this->users->getUser(Session::get(SESSION_USERID));
    $first_name = $user["userFirstName"];
    $last_name = $user["userLastName"];
    $email = $user["userEmail"];
    $display_name = $user["userDisplayName"];
    $description = $user["description"];

    $resp = ['success'=>true,'data'=>['email'=>$email,'first_name'=>$first_name,'last_name'=>$last_name,'display_name'=>$display_name, 'description' => $description]];

    return $this->jsonResponse($resp);
  }


  public function deleteAccount(){
    //TODO
    return $this->display('./');
  }
}
