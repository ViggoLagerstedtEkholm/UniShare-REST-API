<?php
namespace App\Controllers;

class ForumController extends Controller{

  function __construct(){}

  public function view(){
    return $this->display('forum','forum', []);
  }
}
