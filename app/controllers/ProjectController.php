<?php
namespace App\Controllers;
use App\Models\MVCModels\Projects;
use App\Core\Request;
use App\Core\Session;
use App\Core\Application;
use App\Includes\Validate;
use App\Core\ImageHandler;
use App\Middleware\AuthenticationMiddleware;

class ProjectController extends Controller{
  private $projects;
  private $imageHandler;

  function __construct(){
    $this->setMiddlewares(new AuthenticationMiddleware(['view', 'uploadProject', 'deleteProject', 'getProject', 'updateProject']));
    $this->projects = new Projects();
    $this->imageHandler = new ImageHandler();
  }

  public function add(){
      return $this->display('projects/add','projects', []);
  }

  public function update(){
    if(isset($_GET["ID"])){
      $ID = $_GET["ID"];

      $params = [
        "projectID" => $ID
      ];

      return $this->display('projects/update','projects', $params);
    }else{
      Application::$app->redirect("./");
    }
  }

  public function uploadProject(Request $request)
  {
    $fileUploadName = 'project-file';
    $userID = Session::get(SESSION_USERID);

    $body = $request->getBody();
    if($body["customCheck"] == "Off"){
      $params = [
        "link" => $body["link"],
        "name" => $body["name"],
        "description" => $body["description"],
        "project-file" => $fileUploadName,
        "customCheck" => $body["customCheck"]
      ];
    }else{
      $params = [
        "link" => $body["link"],
        "name" => $body["name"],
        "description" => $body["description"],
        "custom" => $body["custom"],
        "customCheck" => $body["customCheck"]
      ];
    }

    //Check all fields + image validity
    $errors = $this->projects->validate($params);

    if(count($errors) > 0){
      $errorList = http_build_query(array('error' => $errors));
      Application::$app->redirect("../profile?ID=$userID&$errorList");
      exit();
    }

    if($params["customCheck"] == "On"){
      $image = $this->imageHandler->createImageFromText($params["custom"]);
    }else{
      $originalImage = $_FILES[$fileUploadName];
      $image = $this->imageHandler->handleUploadResizing($originalImage);
    }

    $this->projects->uploadProject($params, $userID, $image);
    Application::$app->redirect("../profile?ID=$sessionID");
  }

  public function deleteProject(Request $request){
    $courseRequest = $request->getBody();

    $projectID = $courseRequest["projectID"];
    $canRemove = $this->projects->checkIfUserOwner(Session::get(SESSION_USERID), $projectID);

    if($canRemove){
      $this->projects->deleteProject($projectID);
      $resp = ['success'=>true,'data'=>['Status'=>true, 'ID'=>$projectID]];
      return $this->jsonResponse($resp, 200);
    }else{
      $resp = ['success'=>false,'data'=>['Status'=>false, 'ID'=>$projectID]];
      return $this->jsonResponse($resp, 500);
    }
  }

  public function getProjectForEdit(Request $request){
    $body = $request->getBody();
    $projectID = $body["projectID"];
    $project = $this->projects->getProject($projectID);

    //Check if the currently logged in user is the one that owns the project.
    if($project["userID"] == Session::get(SESSION_USERID)){
      $name = $project["name"];
      $link = $project["link"];
      $description = $project["description"];
      $resp = ['success'=>true,'data'=>['Name' => $name, 'Link' => $link, 'Description' => $description]];
      return $this->jsonResponse($resp, 200);
    }else{
      $resp = ['success'=>false, 'data'=>['Project' => $project]];
      return $this->jsonResponse($resp, 403);
    }
  }

  public function updateProject(Request $request){
    $fileUploadName = 'project-file';
    $userID = Session::get(SESSION_USERID);

    $body = $request->getBody();
    $projectID = $body["projectID"];

    $params = [
      "link" => $body["link"],
      "name" => $body["name"],
      "description" => $body["description"],
      "project-file" => $fileUploadName,
      "customCheck" => "Off"
    ];

    //Check all fields + image validity
    $errors = $this->projects->validate($params);

    if(count($errors) > 0){
      $errorList = http_build_query(array('error' => $errors));
      Application::$app->redirect("../project?ID=$projectID&$errorList");
      exit();
    }

    $canUpdate = $this->projects->checkIfUserOwner($userID, $projectID);

    if($canUpdate){
      $originalImage = $_FILES[$fileUploadName];
      $resizedImage = $this->imageHandler->handleUploadResizing($originalImage);
      $project = $this->projects->updateProject($projectID, $params, $resizedImage);
      Application::$app->redirect("../profile?ID=$userID");
    }else{
      Application::$app->redirect("../");
    }
  }
}
