<?php
namespace App\Controllers;
use App\Core\Application;
use App\Core\Session;
use App\Core\Request;
use App\Models\MVCModels\Degrees;
use App\Models\MVCModels\Users;
use App\Models\Templates\Degree;
use App\Middleware\AuthenticationMiddleware;

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

  public function add(){
    return $this->display('degrees/add','degrees', []);
  }

  public function update(){
    return $this->display('degrees/update','degrees', []);
  }

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
      Application::$app->redirect("../profile?ID=$userID&$errorList");
      exit();
    }

    $this->degrees->uploadDegree($params, Session::get(SESSION_USERID));
    Application::$app->redirect("../profile?ID=$ID");
  }
  
  public function getDegrees(){
    $degrees = $this->degrees->getDegrees(Session::get(SESSION_USERID));
    $resp = ['success'=>true,'data'=>['degrees'=>$degrees]];
    return $this->jsonResponse($resp, 200);
  }

  public function updateDegree(Request $request){

  }

  public function deleteDegree(Request $request){

  }
}
?>
