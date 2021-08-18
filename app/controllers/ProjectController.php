<?php
namespace App\Controllers;
use App\Models\MVCModels\Projects;

class ProjectController extends Controller{
  private $projects;

  function __construct(){
    $this->projects = new Projects();
  }

  public function view(){
    if(isset($_GET["ID"])){
      $ID = $_GET["ID"];

      $project = $this->projects->getProject($ID);

      $params = [
        "project" => $project
      ];

      return $this->display('projects','projects', $params);
    }else{
      Application::$app->redirect("./");
    }
  }
}
