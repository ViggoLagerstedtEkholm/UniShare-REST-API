<?php
namespace App\Controllers;
use App\Core\Application;
use App\Core\Request;
use App\Core\Session;
use App\Core\ImageHandler;
use App\Models\MVCModels\Profiles;
use App\Models\MVCModels\Users;
use App\Models\MVCModels\Projects;
use App\Models\MVCModels\Degrees;
use App\Includes\Validate;
use App\Includes\Constants;
use App\Models\Templates\Project;
use App\Middleware\AuthenticationMiddleware;

class ProfileController extends Controller
{
  public function __construct(){
    $this->setMiddlewares(new AuthenticationMiddleware(['uploadImage', 'uploadProject', 'deleteProject', 'pubishCourse', 'getDegrees']));

    $this->imageHandler = new ImageHandler();
    $this->profiles = new Profiles();
    $this->users = new Users();
    $this->projects = new Projects();
    $this->degrees = new Degrees();
  }

  public function view(Request $request)
  {
    if(isset($_GET["ID"])){
      $ID = $_GET["ID"];

      if(!empty($ID)){
        $user = $this->users->getUser($ID);
        $first_name = $user["userFirstName"];
        $last_name = $user["userLastName"];
        $image = base64_encode($user["userImage"]);
        $date = $user["lastOnline"];
        $display_name = $user["userDisplayName"];
        $privilege = $user["privilege"];

        $degrees = $this->degrees->getDegrees($ID);

        $updatedVisitCount = $this->profiles->addVisitor($ID, $user);
        $projects = $this->projects->getProjects($ID);

        if(Session::isLoggedIn()){
          $sessionID = Session::get(SESSION_USERID);

          if($ID == $sessionID){
            $date = $this->profiles->addVisitDate($sessionID);
          }
        }

        $params = [
          'image' => $image,
          'degrees' => $degrees,
          'updatedVisitCount' => $updatedVisitCount,
          'projects' => $projects,
          'currentPageID' => $ID,
          'visitDate' => $date,
          'first_name' => $first_name,
          'last_name' => $last_name,
          'display_name' => $display_name,
          'privilege' => $privilege
        ];
        return $this->display('profile','profile', $params);
      }
    }
    Application::$app->redirect("./");
  }

  public function uploadImage(Request $request){
    $sessionID = Session::get(SESSION_USERID);

    $image_object = Validate::validateImage('file');

    if($image_object != false){
      $image_resize = $this->imageHandler->handleUploadResizing($image_object);
      $this->profiles->uploadImage($image_resize, $sessionID);
      Application::$app->redirect("../../profile?ID=$sessionID");
    }else{
      Application::$app->redirect("../../profile?ID=$sessionID&error=" . INVALID_UPLOAD);
    }
  }

  public function uploadProject(Request $request)
  {
    $sessionID = Session::get(SESSION_USERID);

    $project = new Project();
    $project->populateAttributes($request->getBody());

    if(Validate::hasInvalidProjectLink($project->link) === true){
      Application::$app->redirect("../../profile?ID=$sessionID&error=" . INVALID_PROJECT_LINK);
      exit();
    }

    $image_object = Validate::validateImage('project-file');
    $hasSelectedCustomText = $project->customCheck == "on";
    if($image_object != false || $hasSelectedCustomText){
        if($project->customCheck){
          $project->image = $this->imageHandler->createImageFromText($project->custom);
        }else{
          $project->image = $this->imageHandler->handleUploadResizing($image_object);
        }

        if(Validate::hasEmptyProject($project) === true){
          Application::$app->redirect("../../profile?ID=$sessionID&error=" . EMPTY_PROJECT);
          exit();
        }

        $success = $this->projects->uploadProject($project, $sessionID);

        if($success){
          Application::$app->redirect("../../profile?ID=$sessionID");
        }else{
          Application::$app->redirect("../../profile?ID=$sessionID&error=" . INVALID_UPLOAD);
        }
      }
    else{
      Application::$app->redirect("../../profile?ID=$sessionID&error=" . INVALID_UPLOAD);
    }
  }

  public function deleteProject(Request $request){
    $sessionID = Session::get(SESSION_USERID);

    foreach($request->getBody() as $key => $value){
       $this->projects->deleteProject($key, $sessionID);
     }

     Application::$app->redirect("../../profile?ID=$sessionID");
  }
}
