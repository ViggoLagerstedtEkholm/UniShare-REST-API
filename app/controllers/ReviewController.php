<?php

namespace App\controllers;

use App\Middleware\AuthenticationMiddleware;
use App\Models\Reviews;
use App\Core\Session;
use App\Core\Request;
use App\Core\Application;

/**
 * Review controller for handling reviews.
 * @author Viggo Lagestedt Ekholm
 */
class ReviewController extends Controller
{
    private Reviews $reviews;

    function __construct()
    {
        $this->setMiddlewares(new AuthenticationMiddleware(['setRate', 'getRate', 'uploadReview', 'deleteReview', 'getReview']));

        $this->reviews = new Reviews();
    }

    /**
     * This method handles getting review by course ID.
     * @param Request $request
     * @return false|string
     */
    public function getReview(Request $request): bool|string
    {
        $body = $request->getBody();
        $courseID = $body['courseID'];
        $userID = Session::get(SESSION_USERID);

        $result = $this->reviews->getReview($userID, $courseID);
        $resp = ['success' => true, 'data' => ['result' => $result]];
        return $this->jsonResponse($resp, 200);
    }

    /**
     * This method handles deleting reviews by course ID and user ID (many to many table).
     * @param Request $request
     * @return bool|string|null
     */
    public function deleteReview(Request $request): bool|string|null
    {
        $body = $request->getBody();

        $courseID = $body['courseID'];
        $userID = $body['userID'];

        if ($userID == Session::get(SESSION_USERID)) {
            $this->reviews->deleteReview($userID, $courseID);
            return $this->jsonResponse(true, 200);
        } else {
            return $this->jsonResponse(true, 500);
        }
    }

    /**
     * This method handles uploading reviews.
     * @param Request $request
     * @return false|string
     */
    public function uploadReview(Request $request): bool|string
    {
        $body = $request->getBody();

        $params = [
            "courseID" => $body["courseID"],
            "fulfilling" => $body["fulfilling"],
            "environment" => $body["environment"],
            "difficulty" => $body["difficulty"],
            "grading" => $body["grading"],
            "literature" => $body["literature"],
            "overall" => $body["overall"],
            "text" => $body["text"],
        ];

        $errors = $this->reviews->validate($params);

        if (count($errors) > 0) {
            $errorList = http_build_query(array('error' => $errors));
            return $this->jsonResponse($errorList, 500);
        }

        $success = $this->reviews->insertReview($params);

        if (!$success) {
            $resp = ['success' => false, 'data' => ['Status' => 'Failed upload review']];
            return $this->jsonResponse($resp, 500);
        }else{
            return $this->jsonResponse(true, 200);
        }
    }
}
