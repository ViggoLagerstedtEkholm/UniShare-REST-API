<?php

namespace App\controllers;

use App\core\Exceptions\NotFoundException;
use App\Middleware\AuthenticationMiddleware;
use App\Models\Courses;
use App\Models\Reviews;
use App\Core\Session;
use App\Core\Request;

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
     * Get statistics from a given course.
     * @param Request $request
     * @return false|string
     */
    public function getCourseStatistics(Request $request): bool|string
    {
        $body = $request->getBody();
        $ID = $body['courseID'];

        $result = $this->courses->getArithmeticMeanScore($ID);
        $POPULARITY_RANK = $this->courses->getPopularityRank($ID)->fetch_assoc()["POPULARITY_RANK"] ?? "Not set!";
        $RATING_RANK = $this->courses->getOverallRankingRating($ID)->fetch_assoc()["RATING_RANK"] ?? "Not set!";
        $review_count = $this->reviews->getReviewCount($ID);

        $arithmeticMean = $result["AVG(rating)"];
        $COUNT = $result["COUNT(rating)"];

        $params = [
            "score" => $arithmeticMean,
            "total_votes" => $COUNT,
            "POPULARITY_RANK" => $POPULARITY_RANK,
            "RATING_RANK" => $RATING_RANK,
            "review_count" => $review_count
        ];

        return $this->jsonResponse($params, 200);
    }

    /**
     * Get course by ID.
     * @param Request $request
     * @return false|string
     */
    public function getCourse(Request $request): bool|string
    {
        $body = $request->getBody();
        $ID = $body['courseID'];
        $course = $this->courses->getCourse($ID);
        return $this->jsonResponse($course, 200);
    }

    /**
     * This method gets the rating graph data from the course ID.
     * @param Request $request
     * @return false|string
     */
    public function getRatingGraphData(Request $request): bool|string
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
    public function setRate(Request $request): bool|string
    {
        $body = $request->getBody();
        $courseID = $body["courseID"];
        $rating = $body["rating"];
        $this->courses->setRate(Session::get(SESSION_USERID), $courseID, $rating);
        $resp = ['success' => true, 'data' => ['rating' => $rating]];
        return $this->jsonResponse($resp, 200);
    }

    /**
     * This method gets the rating from the logged in user.
     * @param Request $request
     * @return false|string
     */
    public function getRate(Request $request): bool|string
    {
        $body = $request->getBody();
        $courseID = $body["courseID"];
        $rating = $this->courses->getRate(Session::get(SESSION_USERID), $courseID);
        $resp = ['success' => true, 'data' => ['rating' => $rating]];
        return $this->jsonResponse($resp, 200);
    }
}
