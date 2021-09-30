<?php

namespace App\controllers;

use App\core\Handler;
use App\Core\Session;
use App\Middleware\AuthenticationMiddleware;
use App\Models\Reviews;

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
     * @param Handler $handler
     * @return false|string
     */
    public function getReview(Handler $handler): bool|string
    {
        $body = $handler->getRequest()->getBody();
        $courseID = $body['courseID'];
        $userID = Session::get(SESSION_USERID);

        $result = $this->reviews->getReview($userID, $courseID);
        $resp = ['success' => true, 'data' => ['result' => $result]];
        return $handler->getResponse()->jsonResponse($resp, 200);
    }

    /**
     * This method handles deleting reviews by course ID and user ID (many to many table).
     * @param Handler $handler
     * @return bool|string|null
     */
    public function deleteReview(Handler $handler): bool|string|null
    {
        $body = $handler->getRequest()->getBody();

        $courseID = $body['courseID'];
        $userID = $body['userID'];

        if ($userID == Session::get(SESSION_USERID)) {
            $this->reviews->deleteReview($userID, $courseID);
            return $handler->getResponse()->jsonResponse(true, 200);
        } else {
            return $handler->getResponse()->jsonResponse(true, 500);
        }
    }

    /**
     * This method handles uploading reviews.
     * @param Handler $handler
     * @return bool|string|null
     */
    public function uploadReview(Handler $handler): bool|string|null
    {
        $body = $handler->getRequest()->getBody();

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
            return $handler->getResponse()->jsonResponse($errors, 500);
        }

        $success = $this->reviews->insertReview($params);

        if (!$success) {
            $handler->getResponse()->setStatusCode(500);
        } else {
            $handler->getResponse()->setStatusCode(200);
        }
        return null;
    }
}
