<?php

namespace App\controllers;

use App\core\Handler;
use App\Core\Session;
use App\Middleware\AuthenticationMiddleware;
use App\Models\Courses;
use App\Models\Reviews;

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
     * @param Handler $handler
     * @return false|string
     */
    public function getCourseStatistics(Handler $handler): bool|string
    {
        $body = $handler->getRequest()->getBody();
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

        return $handler->getResponse()->jsonResponse($params, 200);
    }

    /**
     * Get course by ID.
     * @param Handler $handler
     * @return false|string
     */
    public function getCourse(Handler $handler): bool|string
    {
        $body = $handler->getRequest()->getBody();
        $ID = $body['courseID'];
        $course = $this->courses->getCourse($ID);
        return $handler->getResponse()->jsonResponse($course, 200);
    }

    /**
     * This method gets the rating graph data from the course ID.
     * @param Handler $handler
     * @return false|string
     */
    public function getRatingGraphData(Handler $handler): bool|string
    {
        $ratingRequest = $handler->getRequest()->getBody();
        $courseID = $ratingRequest["courseID"];

        $ratings = $this->courses->getGraphData($courseID);
        $resp = ['data' => ['ratings' => $ratings]];

        return $handler->getResponse()->jsonResponse($resp, 200);
    }

    /**
     * This method sets the rating from the logged in user.
     * @param Handler $handler
     * @return false|string
     */
    public function setRate(Handler $handler): bool|string
    {
        $body = $handler->getRequest()->getBody();
        $courseID = $body["courseID"];
        $rating = $body["rating"];
        $this->courses->setRate(Session::get(SESSION_USERID), $courseID, $rating);
        $resp = ['success' => true, 'data' => ['rating' => $rating]];
        return $handler->getResponse()->jsonResponse($resp, 200);
    }

    /**
     * This method gets the rating from the logged in user.
     * @param Handler $handler
     * @return false|string
     */
    public function getRate(Handler $handler): bool|string
    {
        $body = $handler->getRequest()->getBody();
        $courseID = $body["courseID"];
        $rating = $this->courses->getRate(Session::get(SESSION_USERID), $courseID);
        $resp = ['success' => true, 'data' => ['rating' => $rating]];
        return $handler->getResponse()->jsonResponse($resp, 200);
    }
}
