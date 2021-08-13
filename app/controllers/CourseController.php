<?php
namespace App\Controllers;
use App\Middleware\AuthenticationMiddleware;
use App\Models\MVCModels\Users;
use App\Models\MVCModels\Courses;
use App\Core\Session;
use App\Core\Response;
use App\Core\Request;
use App\Includes\Validate;
use App\Core\Application;

class CourseController extends Controller{
  function __construct(){
    $this->setMiddlewares(new AuthenticationMiddleware(['setRate', 'getRate']));
    $this->courses = new Courses();
    $this->users = new Users();
  }

  public function view(){
    if(isset($_GET["ID"])){
      $ID = $_GET["ID"];
      $course = $this->courses->getCourse($ID);
      $result = $this->courses->getArthimetricMeanScore($ID);
      $SUM = $result["SUM(rating)"];
      $COUNT = $result["COUNT(*)"];

      $arthimetricMean = $SUM / $COUNT;
      $userRating = null;
      if(Session::isLoggedIn()){
        $userRating = $this->courses->getRate(Session::get(SESSION_USERID), $ID);
      }

      $params = [
        "rating" => $userRating,
        "course" => $course,
        "score" => $arthimetricMean,
        "total_votes" => $COUNT
      ];

      return $this->display('courses','courses', $params);
    }
    Application::$app->redirect('./');
  }

  public function setRate(Request $request){
    $ratingRequest = $request->getBody();
    $courseID = $ratingRequest["courseID"];
    $rating = $ratingRequest["rating"];

    $this->courses->setRate(Session::get(SESSION_USERID), $courseID, $rating);
  }

  public function getRate(Request $request){
    $ratingRequest = $request->getBody();
    $courseID = $ratingRequest["courseID"];
    $rating = $this->courses->getRate(Session::get(SESSION_USERID), $courseID);
    $resp = ['success'=>true,'data'=>['rating'=>$rating]];
    return $this->jsonResponse($resp);
  }
}
