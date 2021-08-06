<?php
namespace App\Controllers;
use App\Models\MVCModels;

class ProjectsController extends MVCModels\Projects implements IDatabase{
  public function delete_project($ID, $currentID){
    $this->DeleteProject($ID, $currentID);
  }

  public function upload_project($project, $ID){
    $this->uploadProject($project, $ID);
  }

  public function get_projects($ID){
    return $this->getProjects($ID);
  }

  public function get_max_id(){
    return $this->GetMaxID();
  }

  public function connect_database(){
    $this->connect();
  }

  public function close_database(){
    $this->close();
  }
}
