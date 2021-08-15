<?php
namespace App\Controllers;
use App\Core\Application;
use App\Core\Session;
use App\Core\Request;
use App\Includes\Validate;
use App\Models\MVCModels\Degrees;
use App\Models\MVCModels\Users;
use App\Models\Templates\Degree;
use App\Middleware\AuthenticationMiddleware;

class DegreeController extends Controller
{
  public function __construct()
  {
    $this->setMiddlewares(new AuthenticationMiddleware(['uploadDegree', 'deleteDegree', 'getDegrees', 'addCourse']));
    $this->degrees = new Degrees();
    $this->users = new Users();
  }

  public function uploadDegree(Request $request){
    $degree = new Degree();
    $degree->populateAttributes($request->getBody());

    $error = array();
    if(Validate::hasEmptyInputDegree($degree) === true){
      $error [] = EMPTY_FIELDS;
    }
    if(Validate::hasInvalidDates($degree->start_date, $degree->end_date) === true){
      $error [] = INVALID_DATES;
    }

    $URL;
    $errorCount = count($error);
    if($errorCount > 0){
      for ($x = 0; $x < $errorCount; $x++) {
        $x == $errorCount - 1 ? $URL .= $error[$x] : $URL .= $error[$x] . "&error=";
      }
      $ID = Session::get(SESSION_USERID);
      Application::$app->redirect("../profile?ID=$ID&error=" . $URL);
      exit();
    }

    $this->degrees->uploadDegree($degree, Session::get(SESSION_USERID));
    Application::$app->redirect("../profile?ID=$ID");
  }

  public function deleteDegree(Request $request){
    
  }

  public function getDegrees(){
    $degrees = $this->degrees->getDegrees(Session::get(SESSION_USERID));
    $resp = ['success'=>true,'data'=>['degrees'=>$degrees]];
    return $this->jsonResponse($resp);
  }
}
?>
