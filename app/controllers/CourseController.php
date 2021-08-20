<?php
namespace App\Controllers;
use App\Middleware\AuthenticationMiddleware;
use App\Models\MVCModels\Courses;
use App\Models\MVCModels\Reviews;
use App\Core\Session;
use App\Core\Request;
use App\Core\Application;
use App\Includes\Validate;
use App\Models\Templates\Review;

class CourseController extends Controller{
  private $courses;
  private $reviews;
  
  function __construct(){
    $this->setMiddlewares(new AuthenticationMiddleware(['setRate', 'getRate', 'uploadReview', 'deleteReview']));
    $this->courses = new Courses();
    $this->reviews = new Reviews();
  }

  public function view(){
    if(isset($_GET["ID"])){
      $ID = $_GET["ID"];
      if(!empty($ID)){
        $course = $this->courses->getCourse($ID);
        $result = $this->courses->getArthimetricMeanScore($ID);
        $reviews = $this->courses->getReviews($ID);
        $amountOfReviews = count($reviews);
        $POPULARITY_RANK = $this->courses->getPopularityRank($ID)->fetch_assoc()["POPULARITY_RANK"] ?? "Not set!";
        $RATING_RANK = $this->courses->getOverallRankingRating($ID)->fetch_assoc()["RATING_RANK"] ?? "Not set!";

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
    }
    Application::$app->redirect('./');
  }

  public function setRate(Request $request){
    $ratingRequest = $request->getBody();
    $courseID = $ratingRequest["courseID"];
    $rating = $ratingRequest["rating"];

    $this->courses->setRate(Session::get(SESSION_USERID), $courseID, $rating);
    $resp = ['success'=>true,'data'=>['rating'=>$rating]];
    return $this->jsonResponse($resp, 200);
  }

  public function getRate(Request $request){
    $ratingRequest = $request->getBody();
    $courseID = $ratingRequest["courseID"];
    $rating = $this->courses->getRate(Session::get(SESSION_USERID), $courseID);
    $resp = ['success'=>true,'data'=>['rating'=>$rating]];
    return $this->jsonResponse($resp, 200);
  }

  public function review(){
    return $this->display('courses','review', []);
  }
  
  public function deleteReview(Request $request){
    $body = $request->getBody();
    
    $reviewID = $body['reviewID'];
    
    //TODO
  }
  
  public function updateReview(Request $request){
    $body = $request->getBody();
    //TODO

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
      Application::$app->redirect("../../review?ID=$courseID&$errorList");
      exit();
    }
    
    $success = $this->courses->insertReview($params);
    
    if($success){
      Application::$app->redirect("../../courses?ID=$review->courseID&success=true");
    }else{
      $resp = ['success'=>false,'data'=>['Status'=>'Failed upload review']];
      return $this->jsonResponse($resp, 500);
    }
  }
}
