<?php
namespace App\Controllers;
use App\Middleware\AuthenticationMiddleware;
use App\Models\MVCModels\Reviews;
use App\Core\Session;
use App\Core\Request;
use App\Core\Application;
use App\Includes\Validate;

class ReviewController extends Controller{
  private $reviews;

  function __construct(){
    $this->setMiddlewares(new AuthenticationMiddleware(['setRate', 'getRate', 'uploadReview', 'deleteReview']));
    $this->reviews = new Reviews();
  }
  
  public function review(){
    return $this->display('review','review', []);
  }
  
  public function getReview(Request $request){
    $body = $request->getBody();
    $courseID = $body['courseID'];
    $userID = Session::get(SESSION_USERID);
    
    $result = $this->reviews->getReview($userID, $courseID);
    $resp = ['success'=>true,'data'=>['result' => $result]];
    return $this->jsonResponse($resp, 200);
  }

  public function deleteReview(Request $request){
    $body = $request->getBody();

    $courseID = $body['courseID'];
    $userID = $body['userID'];
    
    if($userID == Session::get(SESSION_USERID)){
      $this->reviews->deleteReview($userID, $courseID);
      $resp = ['success'=>true,'data'=>['userID' => $userID, 'courseID' => $courseID]];
      return $this->jsonResponse($resp, 200);
    }else{
      $resp = ['success'=>false];
      return $this->jsonResponse($resp, 401);
    }
  }

  public function uploadReview(Request $request){
    $body = $request->getBody();

    $params = [
      "courseID" => $body["courseID"],
      "fulfilling" => $body["fulfilling"],
      "environment" => $body["environment"],
      "difficulty" => $body["difficulty"],
      "grading" => $body["grading"],
      "litterature" => $body["litterature"],
      "overall" => $body["overall"],
      "text" => $body["text"],
    ];

    $errors = $this->reviews->validate($params);

    $courseID = $params['courseID'];
    if(count($errors) > 0){
      $errorList = http_build_query(array('error' => $errors));
      Application::$app->redirect("/UniShare/review?ID=$courseID&$errorList");
      exit();
    }

    $success = $this->reviews->insertReview($params);

    if($success){
      Application::$app->redirect("/UniShare/courses?ID=$courseID");
      exit();
    }else{
      $resp = ['success'=>false,'data'=>['Status'=>'Failed upload review']];
      return $this->jsonResponse($resp, 500);
    }
  }
}
