<?php
namespace App\Controllers;
use App\Core\Application;
use App\Core\Session;
use App\Core\Request;
use App\Models\MVCModels\Degrees;
use App\Models\MVCModels\Users;
use App\Models\MVCModels\Degree;
use App\Middleware\AuthenticationMiddleware;

/**
 * Degree controller for handling degrees.
 * @author Viggo Lagestedt Ekholm
 */
class DegreeController extends Controller
{
  private $degrees;
  private $users;

  public function __construct()
  {
    $this->setMiddlewares(new AuthenticationMiddleware(['view', 'uploadDegree', 'deleteDegree', 'updateDegree', 'getdegrees']));

    $this->degrees = new Degrees();
    $this->users = new Users();
  }

  /**
   * This method shows the add degree page.
   * @return View
   */
  public function add(){
    return $this->display('degrees/add','degrees', []);
  }

  /**
   * This method shows the update degree page.
   * @return View
   */
  public function update(){
    if(isset($_GET["ID"])){
      $ID = $_GET["ID"];

      $params = [
        'degreeID' => $ID
      ];

      return $this->display('degrees/update','degrees', $params);
    }
  }

  /**
   * This method handles uploading a degree to a user.
   * @param Request sanitized request from the user.
   */
  public function uploadDegree(Request $request){
    $degree = new Degree();
    $body = $request->getBody();
    $userID = Session::get(SESSION_USERID);

    $params = [
      "name" => $body["name"],
      "field_of_study" => $body["field_of_study"],
      "start_date" => $body["start_date"],
      "end_date" => $body["end_date"],
      "country" => $body["country"],
      "city" => $body["city"],
      "university" => $body["university"],
    ];

    $errors = $this->degrees->validate($params);

    if(count($errors) > 0){
      $errorList = http_build_query(array('error' => $errors));
      Application::$app->redirect("/UniShare/profile?ID=$userID&$errorList");
      exit();
    }

    $this->degrees->uploadDegree($params, Session::get(SESSION_USERID));
    Application::$app->redirect("/UniShare/profile?ID=$userID");
  }

  /**
   * This method gets the degrees from the logged in user.
   * @return JSON encoded string 200(OK)
   */
  public function getDegrees(){
    $degrees = $this->degrees->getDegrees(Session::get(SESSION_USERID));
    $resp = ['success'=>true,'data'=>['degrees'=>$degrees]];
    return $this->jsonResponse($resp, 200);
  }

  /**
   * This method gets a specific degree from ID from the logged in user.
   * @param Request sanitized request from the user.
   * @return JSON encoded string 404(Not found) | 200(OK) | 500(generic error response)
   */
  public function getDegree(Request $request){
    $body = $request->getBody();
    $degreeID = $body["degreeID"];

    if(!empty($degreeID)){
      $degree = $this->degrees->getDegree($degreeID);
    }else{
      $resp = ['success'=>false, 'status' => 'No matching ID!'];
      return $this->jsonResponse($resp, 404);
    }

    if(!is_null($degree)){
      $resp = ['success'=>true,'data'=>['degree' => $degree[0]]];
      return $this->jsonResponse($resp, 200);
    }else{
      $resp = ['success'=>false];
      return $this->jsonResponse($resp, 500);
    }
  }

  /**
   * This method updates a specific degree from ID from the logged in user.
   * @param Request sanitized request from the user.
   */
  public function updateDegree(Request $request){
    $body = $request->getBody();
    $degreeID = $body["degreeID"];

    $userID = Session::get(SESSION_USERID);

    $errors = $this->degrees->validate($body);

    if(count($errors) > 0){
      $errorList = http_build_query(array('error' => $errors));
      Application::$app->redirect("/UniShare/degree/update?ID=$degreeID&$errorList");
      exit();
    }

    $canUpdate = $this->degrees->checkIfUserOwner($userID, $degreeID);

    if($canUpdate){
      $this->degrees->updateDegree($body, $userID);
      Application::$app->redirect("/UniShare/profile?ID=$userID");
    }else{
      Application::$app->redirect("/UniShare/");
    }
  }

  /**
   * This method deletes a specific degree from ID from the logged in user.
   * @param Request sanitized request from the user.
   * @return JSON encoded string 200(OK) | 401(Unauthorized)
   */
  public function deleteDegree(Request $request){
    $body = $request->getBody();
    $degreeID = $body['degreeID'];
    $userID = Session::get(SESSION_USERID);

    $canDelete = $this->degrees->checkIfUserOwner($userID, $degreeID);

    if($canDelete){
      $this->degrees->deleteDegree($degreeID);
      $resp = ['success'=>true,'data'=>['degreeID' => $degreeID]];
      return $this->jsonResponse($resp, 200);
    }else{
      $resp = ['success'=>false];
      return $this->jsonResponse($resp, 401);
    }
  }
}
