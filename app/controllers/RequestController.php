<?php
namespace App\Controllers;
use App\Core\Request;
use App\Models\MVCModels\Requests;
use App\Core\Session;
use App\Middleware\AuthenticationMiddleware;
use App\Core\Application;

class RequestController extends Controller{
  private $requests;

  function __construct(){
    $this->setMiddlewares(new AuthenticationMiddleware(['view', 'uploadRequest', 'deletePending']));
    $this->requests = new Requests();
  }

  public function view(){
    $requests = $this->requests->getRequestedCourses();
    $params = [
      "requests" => $requests
    ];

    return $this->display('request','request', $params);
  }

  public function uploadRequest(Request $request){
    $courseRequest = $request->getBody();

    $params = [
      "name" => $courseRequest["name"],
      "credits" => $courseRequest["credits"],
      "duration" => $courseRequest["duration"],
      "country" => $courseRequest["country"],
      "city" => $courseRequest["city"],
      "university" => $courseRequest["university"],
      "description" => $courseRequest["description"]
    ];

    $errors = $this->requests->validate($params);

    if(count($errors) > 0){
      $query = http_build_query(array('error' => $errors));
      Application::$app->redirect("../request?$query");

    }else{
      $success = $this->requests->insertRequestedCourse($params, Session::get(SESSION_USERID));
      Application::$app->redirect("../request?error=none");
    }
  }

  public function updateRequest(Request $request){
    //TODO
  }

  function deletePending(Request $request){
    $courseRequest = $request->getBody();

    $requestID = $courseRequest["requestID"];
    $canRemove = $this->requests->checkIfUserOwner(Session::get(SESSION_USERID), $requestID);

    if($canRemove){
      $this->requests->deleteRequest($requestID);
      $resp = ['success'=>true,'data'=>['Status'=>true, 'ID'=>$requestID]];
      return $this->jsonResponse($resp, 200);
    }else{
      $resp = ['success'=>false,'data'=>['Status'=>false, 'ID'=>$requestID]];
      return $this->jsonResponse($resp, 500);
    }
  }
}
