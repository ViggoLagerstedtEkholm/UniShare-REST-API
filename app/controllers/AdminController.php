<?php
namespace App\Controllers;
use App\Core\Application;
use App\Core\Request;
use App\Includes\Validate;
use App\Models\MVCModels\Users;
use App\Models\MVCModels\Courses;
use App\Models\MVCModels\Requests;
use App\Models\Templates\Course;

use App\Core\Session;
use App\Middleware\AuthenticationMiddleware;

class AdminController extends Controller{
  private $users;
  private $courses;
  private $requests;
  
  public function __construct(){
    $this->setMiddlewares(new AuthenticationMiddleware(['view', 'updateUser','removeUser', 'addUser', 'addCourse'. 'removeCourse', 'updateCourse'], true));
    $this->users = new Users();
    $this->courses = new Courses();
    $this->requests = new Requests();
  }

  public function view(){
    $requests = $this->requests->getRequestedCourses();
    
    $params = [
      "requests" => $requests
    ];

    return $this->display('admin', 'admin', $params);
  }

  public function addCourse(Request $request){
    $course = new Course();
    $course->populateAttributes($request->getBody());
    $hasSucceded = $this->courses->insertCourse($course);

    if($hasSucceded){
      $resp = ['success'=>true,'data'=>['Status'=>true]];
      return $this->jsonResponse($resp);
    }else{
      $resp = ['success'=>true,'data'=>['Status'=>false]];
      return $this->jsonResponse($resp);
    }
  }

  public function approveRequest(Request $request){
    $body = $request->getBody();
    $requestID = $body["requestID"];
    
    $success = $this->requests->approveRequest($requestID);
    
    if($success){
      $resp = ['success'=>true,'data'=>['Status'=>true, 'ID'=>$requestID]];
      return $this->jsonResponse($resp, 200);
    }else{
      $resp = ['success'=>false,'data'=>['Status'=>false]];
      return $this->jsonResponse($resp, 500);
    }
  }
  
  public function denyRequest(Request $request){
    $body = $request->getBody();
    $requestID = $body["requestID"];
    
    $success = $this->requests->denyRequest($requestID);
    
    if($success){
      $resp = ['success'=>true,'data'=>['Status'=>true, 'ID'=>$requestID]];
      return $this->jsonResponse($resp, 200);
    }else{
      $resp = ['success'=>false,'data'=>['Status'=>false]];
      return $this->jsonResponse($resp, 500);
    }
  }
}
