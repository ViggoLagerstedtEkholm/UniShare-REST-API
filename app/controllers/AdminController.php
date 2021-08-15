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
  public function __construct(){
    $this->setMiddlewares(new AuthenticationMiddleware(['view', 'updateUser','removeUser', 'addUser', 'addCourse'. 'removeCourse', 'updateCourse'], true));
    $this->users = new Users();
    $this->courses = new Courses();
  }

  public function view(){
    return $this->display('admin', 'admin', []);
  }

  public function addCourse(Request $request){
    $course = new Course();
    $course->populateAttributes($request->getBody());
    $this->courses->insertCourse($course);
    Application::$app->redirect('../../admin');
  }

  public function updateUser()
  {
    // code...
  }

  public function removeUser()
  {
    // code...
  }

  public function addUser()
  {
    // code...
  }

  public function removeCourse()
  {
    // code...
  }

  public function updateCourse()
  {
    // code...
  }
}
