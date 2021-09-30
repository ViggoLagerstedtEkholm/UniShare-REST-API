<?php

namespace App\controllers;

use App\core\Handler;
use App\Middleware\AuthenticationMiddleware;
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

    public function __construct()
    {
        $this->setMiddlewares(new AuthenticationMiddleware(
            ['getRequestedCourses', 'suspendUser', 'enableUser', 'deleteUser', 'approveRequest', 'denyRequest'],
            true));

        $this->requests = new Requests();
        $this->users = new Users();
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
