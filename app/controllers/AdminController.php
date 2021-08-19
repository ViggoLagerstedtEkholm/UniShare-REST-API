<?php
namespace App\Controllers;
use App\Core\Application;
use App\Core\Request;
use App\Includes\Validate;
use App\Models\MVCModels\Users;
use App\Models\MVCModels\Courses;
use App\Models\Templates\Course;

use App\Core\Session;
use App\Middleware\AuthenticationMiddleware;

class AdminController extends Controller{
  private $users;
  private $courses;

  public function __construct(){
    $this->setMiddlewares(new AuthenticationMiddleware(['view', 'updateUser','removeUser', 'addUser', 'addCourse'. 'removeCourse', 'updateCourse'], true));
    $this->users = new Users();
    $this->courses = new Courses();
  }

  public function view(){
    $courses = $this->courses->getRequestedCourses();

    $params = [
      "courses" => $courses
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
    //Get all the data from the request ID and inser it into courses.
  }
}
