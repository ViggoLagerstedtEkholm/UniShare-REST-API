<?php

namespace App\controllers;

use App\Core\Application;
use App\Core\Request;
use App\core\Response;
use App\Models\Courses;
use App\Models\Requests;
use App\Middleware\AuthenticationMiddleware;
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

    public function getRequestedCourses(): bool|string|null
    {
        $requests = $this->requests->getRequestedCoursesAll();
        return $this->jsonResponse($requests, 200);
    }

    public function deleteUser(Request $request): bool|int
    {
        $body = $request->getBody();
        $userID = $body['userID'];

        if($this->users->terminateAccount($userID)){
            return $this->setStatusCode(200);
        }else{
            return $this->setStatusCode(500);
        }
    }

    public function suspendUser(Request $request): bool|int
    {
        $body = $request->getBody();
        $userID = $body['userID'];

        if($this->users->suspend($userID)){
            return $this->setStatusCode(200);
        }else{
            return $this->setStatusCode(500);
        }
    }

    public function enableUser(Request $request): bool|int
    {
        $body = $request->getBody();
        $userID = $body['userID'];

        if($this->users->enable($userID)){
            return $this->setStatusCode(200);
        }else{
            return $this->setStatusCode(500);
        }
    }

    /**
     * This method handles approving requested courses from users.
     * @param Request $request
     * @return false|string
     * @throws Throwable
     */
    public function approveRequest(Request $request): bool|string
    {
        $body = $request->getBody();
        $requestID = $body["requestID"];

        $success = $this->requests->approveRequest($requestID);

        if ($success) {
            $resp = ['success' => true, 'data' => ['Status' => true, 'ID' => $requestID]];
            return $this->jsonResponse($resp, 200);
        } else {
            $resp = ['success' => false, 'data' => ['Status' => false]];
            return $this->jsonResponse($resp, 500);
        }
    }

    /**
     * This method handles denying requested courses from users.
     * @param Request $request
     * @return false|string
     */
    public function denyRequest(Request $request): bool|string
    {
        $body = $request->getBody();
        $requestID = $body["requestID"];

        $success = $this->requests->denyRequest($requestID);

        if ($success) {
            $resp = ['success' => true, 'data' => ['Status' => true, 'ID' => $requestID]];
            return $this->jsonResponse($resp, 200);
        } else {
            $resp = ['success' => false, 'data' => ['Status' => false]];
            return $this->jsonResponse($resp, 500);
        }
    }
}
