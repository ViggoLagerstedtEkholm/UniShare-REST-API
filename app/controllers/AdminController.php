<?php

namespace App\controllers;

use App\core\Handler;
use App\Middleware\AuthenticationMiddleware;
use App\models\Courses;
use App\Models\Requests;
use App\models\Users;
use Throwable;

/**
 * Admin controller for administering website content.
 * @author Viggo Lagestedt Ekholm
 */
class AdminController extends Controller
{
    private Requests $requests;
    private Users $users;
    private Courses $courses;

    public function __construct()
    {
        $this->setMiddlewares(new AuthenticationMiddleware(
            ['updateCourse', 'getRequestedCourses', 'suspendUser', 'enableUser', 'deleteUser', 'approveRequest', 'denyRequest'],
            true));

        $this->requests = new Requests();
        $this->users = new Users();
        $this->courses = new Courses();
    }

    public function updateCourse(Handler $handler): bool|string|null
    {
        $body = $handler->getRequest()->getBody();

        $params = [
            "name" => $body["name"],
            "credits" => $body["credits"],
            "country" => $body["country"],
            "city" => $body["city"],
            "university" => $body["university"],
            "description" => $body["description"],
            "code" => $body["code"],
        ];

        $errors = $this->requests->validate($params);

        if(count($errors) > 0){
            return $handler->getResponse()->jsonResponse($errors, 422);
        }

        $this->courses->updateCourse($body);
        return $handler->getResponse()->setStatusCode(200);
    }

    public function getRequestedCourses(Handler $handler): bool|string|null
    {
        $requests = $this->requests->getRequestedCoursesAll();
        return $handler->getResponse()->jsonResponse($requests, 200);
    }

    public function deleteUser(Handler $handler)
    {
        $body = $handler->getRequest()->getBody();
        $userID = $body['userID'];

        if($this->users->terminateAccount($userID)){
            $handler->getResponse()->setStatusCode(200);
        }else{
            $handler->getResponse()->setStatusCode(500);
        }
    }

    public function suspendUser(Handler $handler)
    {
        $body = $handler->getRequest()->getBody();
        $userID = $body['userID'];

        if($this->users->suspend($userID)){
            $handler->getResponse()->setStatusCode(200);
        }else{
            $handler->getResponse()->setStatusCode(500);
        }
    }

    public function enableUser(Handler $handler)
    {
        $body = $handler->getRequest()->getBody();
        $userID = $body['userID'];

        if($this->users->enable($userID)){
            $handler->getResponse()->setStatusCode(200);
        }else{
            $handler->getResponse()->setStatusCode(500);
        }
    }

    /**
     * This method handles approving requested courses from users.
     * @param Handler $handler
     * @throws Throwable
     */
    public function approveRequest(Handler $handler)
    {
        $body = $handler->getRequest()->getBody();
        $requestID = $body["requestID"];

        $success = $this->requests->approveRequest($requestID);

        if ($success) {
            $handler->getResponse()->setStatusCode(200);
        } else {
            $handler->getResponse()->setStatusCode(500);
        }
    }

    /**
     * This method handles denying requested courses from users.
     * @param Handler $handler
     */
    public function denyRequest(Handler $handler)
    {
        $body = $handler->getRequest()->getBody();
        $requestID = $body["requestID"];

        $success = $this->requests->denyRequest($requestID);

        if ($success) {
            $handler->getResponse()->setStatusCode(200);
        } else {
            $handler->getResponse()->setStatusCode(500);
        }
    }
}
