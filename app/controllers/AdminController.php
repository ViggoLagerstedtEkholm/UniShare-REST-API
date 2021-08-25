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

/**
 * Admin controller for administering website content.
 * @author Viggo Lagestedt Ekholm
 */
class AdminController extends Controller{
  private $users;
  private $courses;
  private $requests;

  public function __construct(){
    //Add restriction to all methods and set the last parameter as true (require admin for whole controller).
    $this->setMiddlewares(new AuthenticationMiddleware(['view', 'updateUser','removeUser', 'addUser', 'addCourse'. 'removeCourse', 'updateCourse'], true));

    $this->users = new Users();
    $this->courses = new Courses();
    $this->requests = new Requests();
  }

  /**
   * This method gets the requested courses and passes them to the view.
   * @return View
   */
  public function view(){
    $requests = $this->requests->getRequestedCourses();

    $params = [
      "requests" => $requests
    ];

    return $this->display('admin', 'admin', $params);
  }

  /**
   * This method handles adding a course to the database.
   * @param Request sanitized request from the user.
   */
  public function addCourse(Request $request){
    $body = $request->getBody();

    $errors = $this->courses->validate($body);

    if(count($errors) > 0){
      $errorList = http_build_query(array('error' => $errors));
      Application::$app->redirect("/UniShare/admin?$errorList");
      exit();
    }

    $hasSucceded = $this->courses->insertCourse($course);

    if($hasSucceded){
      Application::$app->redirect("/UniShare/admin");
    }else{
      Application::$app->redirect("/UniShare/admin?error=true");
    }
  }

  /**
   * This method handles approving requested courses from users.
   * @param Request sanitized request from the user.
   * @return json_encode 200(OK) | 500(generic error response)
   */
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

  /**
   * This method handles denying requested courses from users.
   * @param Request sanitized request from the user.
   * @return json_encode 200(OK) | 500(generic error response)
   */
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
