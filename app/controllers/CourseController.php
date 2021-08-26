<?php

namespace App\controllers;

use App\Middleware\AuthenticationMiddleware;
use App\Models\MVCModels\Courses;
use App\Models\MVCModels\Reviews;
use App\Core\Session;
use App\Core\Request;
use App\Core\Application;

/**
 * Course controller for course handling.
 * @author Viggo Lagestedt Ekholm
 */
class CourseController extends Controller
{
    private Courses $courses;
    private Reviews $reviews;

    function __construct()
    {
        $this->setMiddlewares(new AuthenticationMiddleware(['setRate', 'getRate', 'uploadReview', 'deleteReview']));

        $this->courses = new Courses();
        $this->reviews = new Reviews();
    }

    /**
     * This method gets the course information and passes it the the view.
     * @return string
     */
    public function view(): string
    {
        if (isset($_GET["ID"])) {
            $ID = $_GET["ID"];
            if (!empty($ID)) {

                if (isset($_GET['page'])) {
                    $page = $_GET['page'];
                } else {
                    $page = 1;
                }

                $review_count = $this->reviews->getReviewCount($ID);

                $offsets = $this->calculateOffsets($review_count, $page, 1);
                $start_page_first_result = $offsets['start_page_first_result'];
                $results_per_page = $offsets['results_per_page'];
                $number_of_pages = $offsets['number_of_pages'];

                $reviews = $this->reviews->getReviews($start_page_first_result, $results_per_page, $ID);

                $course = $this->courses->getCourse($ID);
                $result = $this->courses->getArithmeticMeanScore($ID);
                $POPULARITY_RANK = $this->courses->getPopularityRank($ID)->fetch_assoc()["POPULARITY_RANK"] ?? "Not set!";
                $RATING_RANK = $this->courses->getOverallRankingRating($ID)->fetch_assoc()["RATING_RANK"] ?? "Not set!";
                $amountOfReviews = count($reviews);

                $arithmeticMean = $result["AVG(rating)"];
                $COUNT = $result["COUNT(rating)"];

                $userRating = null;
                if (Session::isLoggedIn()) {
                    $userRating = $this->courses->getRate(Session::get(SESSION_USERID), $ID);
                }

                $params = [
                    "rating" => $userRating,
                    "course" => $course[0],
                    "reviews" => $reviews,
                    'page' => $page,
                    'results_per_page' => $results_per_page,
                    'number_of_pages' => $number_of_pages,
                    'start_page_first_result' => $start_page_first_result,
                    "amountOfReviews" => $amountOfReviews,
                    "score" => $arithmeticMean,
                    "total_votes" => $COUNT,
                    "POPULARITY_RANK" => $POPULARITY_RANK,
                    "RATING_RANK" => $RATING_RANK
                ];

                return $this->display('courses', 'courses', $params);
            }
        }
        Application::$app->redirect('./');
    }

    public function getRatingGraphData(Request $request)
    {
        $ratingRequest = $request->getBody();
        $courseID = $ratingRequest["courseID"];

        $ratings = $this->courses->getGraphData($courseID);
        $resp = ['data' => ['ratings' => $ratings]];

        return $this->jsonResponse($resp, 200);
    }

    /**
     * This method sets the rating from the logged in user.
     * @param Request $request
     * @return false|string
     */
    public function setRate(Request $request)
    {
        $ratingRequest = $request->getBody();
        $courseID = $ratingRequest["courseID"];
        $rating = $ratingRequest["rating"];
        $this->courses->setRate(Session::get(SESSION_USERID), $courseID, $rating);
        $resp = ['success' => true, 'data' => ['rating' => $rating]];
        return $this->jsonResponse($resp, 200);
    }

    /**
     * This method gets the rating from the logged in user.
     * @param Request $request
     * @return false|string
     */
    public function getRate(Request $request)
    {
        $ratingRequest = $request->getBody();
        $courseID = $ratingRequest["courseID"];
        $rating = $this->courses->getRate(Session::get(SESSION_USERID), $courseID);
        $resp = ['success' => true, 'data' => ['rating' => $rating]];
        return $this->jsonResponse($resp, 200);
    }
}
