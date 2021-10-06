<?php

namespace App\controllers;

use App\core\Handler;
use App\Core\Session;
use App\Middleware\AuthenticationMiddleware;
use App\Models\Requests;

/**
 * Request controller for handling course requests.
 * @author Viggo Lagestedt Ekholm
 */
class RequestController extends Controller
{
    private Requests $requests;

    function __construct()
    {
        $this->setMiddlewares(new AuthenticationMiddleware(['uploadRequest', 'deletePending', 'getRequests']));

        $this->requests = new Requests();
    }

    public function getRequests(Handler $handler): bool|string|null
    {
        $requests = $this->requests->getRequestedCoursesFromUser();
        return $handler->getResponse()->jsonResponse($requests, 200);
    }

    /**
     * This method handles uploading new course requests.
     * @param Handler $handler
     * @return bool|string|null
     */
    public function uploadRequest(Handler $handler): bool|string|null
    {
        $courseRequest = $handler->getRequest()->getBody();

        $params = [
            "name" => $courseRequest["name"],
            "credits" => $courseRequest["credits"],
            "country" => $courseRequest["country"],
            "city" => $courseRequest["city"],
            "university" => $courseRequest["university"],
            "description" => $courseRequest["description"],
            "code" => $courseRequest["code"],
            "link" => $courseRequest["link"]
        ];

        $errors = $this->requests->validate($params);

        if (count($errors) > 0) {
            return $handler->getResponse()->jsonResponse($errors, 500);
        }

        $this->requests->insertRequestedCourse($params, Session::get(SESSION_USERID));
        return $handler->getResponse()->jsonResponse(true, 200);
    }

    /**
     * This method handles deleting pending requests.
     * @param Handler $handler
     */
    function deletePending(Handler $handler)
    {
        $courseRequest = $handler->getRequest()->getBody();

        $requestID = $courseRequest["requestID"];
        $canRemove = $this->requests->checkIfUserOwner(Session::get(SESSION_USERID), $requestID);

        if ($canRemove) {
            $this->requests->deleteRequest($requestID);
            $handler->getResponse()->setStatusCode(200);
        } else {
            $handler->getResponse()->setStatusCode(500);
        }
    }
}
