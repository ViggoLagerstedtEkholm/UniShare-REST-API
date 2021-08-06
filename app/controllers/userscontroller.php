<?php
namespace App\Controllers;
use App\Models\MVCModels;

class UsersController extends MVCModels\Users implements IDatabase{
  public function login_user($user){
    $this->login($user);
  }

  public function register_user($user){
    $this->register($user);
  }

  public function get_ShowcaseUsersPage($from, $to, $option, $filterOrder){
    return $this->getShowcaseUsersPage($from, $to, $option, $filterOrder);
  }

  public function get_user($ID){
    return $this->getUser($ID);
  }

  public function get_userCount(){
    return $this->getUserCount();
  }

  public function username_exists($display_name, $ID){
    return $this->usernameExists($display_name, $ID);
  }

  public function connect_database(){
    $this->connect();
  }

  public function close_database(){
    $this->close();
  }
}
