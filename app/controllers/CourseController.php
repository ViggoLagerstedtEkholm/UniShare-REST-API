<?php
namespace App\Controllers;
use App\Middleware\AuthenticationMiddleware;
use App\Models\MVCModels\Courses;
use App\Core\Session;
use App\Core\Request;
use App\Core\Application;
use App\Includes\Validate;
use App\Models\Templates\Review;

class CourseController extends Controller{
  private $courses;

  function __construct(){
    $this->setMiddlewares(new AuthenticationMiddleware(['setRate', 'getRate', 'uploadReview', 'request']));
    $this->courses = new Courses();
  }

  public function view(){
    if(isset($_GET["ID"])){
      $ID = $_GET["ID"];
      $course = $this->courses->getCourse($ID);
      $result = $this->courses->getArthimetricMeanScore($ID);
      $reviews = $this->courses->getReviews($ID);
      $amountOfReviews = count($reviews);
      $POPULARITY_RANK = $this->courses->getPopularityRank($ID)->fetch_assoc()["POPULARITY_RANK"];
      $RATING_RANK = $this->courses->getOverallRankingRating($ID)->fetch_assoc()["RATING_RANK"];

      $arthimetricMean = $result["AVG(rating)"];
      $COUNT = $result["COUNT(rating)"];

      $userRating = null;
      if(Session::isLoggedIn()){
        $userRating = $this->courses->getRate(Session::get(SESSION_USERID), $ID);
      }

      $params = [
        "rating" => $userRating,
        "course" => $course,
        "reviews" => $reviews,
        "amountOfReviews" => $amountOfReviews,
        "score" => $arthimetricMean,
        "total_votes" => $COUNT,
        "POPULARITY_RANK" => $POPULARITY_RANK,
        "RATING_RANK" => $RATING_RANK
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

  public function request(Request $request){
    $courseRequest = $request->getBody();

    $params = [
      "userID" => Session::get(SESSION_USERID),
      "name" => $courseRequest["name"],
      "credits" => $courseRequest["credits"],
      "duration" => $courseRequest["duration"],
      "country" => $courseRequest["country"],
      "city" => $courseRequest["city"],
      "university" => $courseRequest["university"],
      "description" => $courseRequest["description"]
    ];

    $this->courses->insertRequestedCourse($params);
  }

  public function review(){
    return $this->display('courses','review', []);
  }

  public function uploadReview(Request $request){
    $review = new Review();
    $review->populateAttributes($request->getBody());
    $success = $this->courses->insertReview($review);
    if($success){
      Application::$app->redirect("../../courses?ID=$review->courseID&success=true");
    }else{
      Application::$app->redirect("../../review?ID=$review->courseID&error=failed");
    }
  }
}
