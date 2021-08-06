<?php
namespace App\Controllers;
use App\Models\MVCModels;

class ProfileController extends MVCModels\Profiles implements IDatabase{
  public function get_image($ID, $user){
    $this->getImage($ID, $usernameExists);
  }

  public function upload_image($image, $ID){
    $this->uploadImage($image, $ID);
  }

  public function add_visitor($ID, $user){
    $this->addVisitor($ID, $user);
  }

  public function add_visit_date($ID){
    $this->addVisitDate($ID);
  }

  public function connect_database(){
    $this->connect();
  }

  public function close_database(){
    $this->close();
  }
}
