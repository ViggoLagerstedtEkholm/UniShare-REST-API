<?php
namespace App\Controllers;

class RequestController extends Controller{

  function __construct(){
    
  }

  public function view(){
    return $this->display('request','request', []);
  }
}
