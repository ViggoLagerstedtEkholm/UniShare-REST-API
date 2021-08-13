<?php
namespace App\Models\Templates;

class User{
  public $ID;
  public $first_name;
  public $last_name;
  public $email;
  public $display_name;
  public $password;
  public $password_repeat;
  public $image;
  public $last_online;
  public $visitors;

  function __construct($first_name, $last_name, $email, $display_name){
    $this->first_name = $first_name;
    $this->last_name = $last_name;
    $this->email = $email;
    $this->display_name = $display_name;
  }
}
