<?php

namespace App\controllers;

use App\Core\Application;
use App\Core\Request;
use App\Models\Users;
use App\Models\Register;
use App\Models\Login;
use App\Middleware\AuthenticationMiddleware;
use App\Core\Session;

/**
 * Authentication controller for handling login/register/logout.
 * @author Viggo Lagestedt Ekholm
 */
class AuthenticationController extends Controller
{
    private Users $users;
    private Login $login;
    private Register $register;

    public function __construct()
    {
        $this->setMiddlewares(new AuthenticationMiddleware(['logout']));

        $this->users = new Users();
        $this->login = new Login();
        $this->register = new Register();
    }

    /**
     * Login using cookie with session ID.
     */
    public function loginWithCookie()
    {
        $this->login->loginFromCOOKIE();
    }

    /**
     * Logout and redirect to start page.
     * @return bool|string
     */
    public function logout(): bool|string
    {
        $this->users->logout();
        $resp = ['success' => true];
        return $this->jsonResponse($resp, 200);
    }

    /**
     * Get login status.
     * @return bool|string
     */
    public function isLoggedIn(): bool|string
    {
        $isLoggedIn = Session::isLoggedIn();
        $resp = ['success' => true, 'data' => ['LoggedIn' => $isLoggedIn]];
        return $this->jsonResponse($resp, 200);
    }

    /**
     * This method handles logging in a user.
     * @param Request $request
     * @return bool|string
     */
    public function login(Request $request): bool|string
    {
        $body = $request->getBody();

        $params = [
            'email' => $body["email"],
            'password' => $body["password"],
            'rememberMe' => $body['rememberMe']
        ];

        $errors = $this->login->validate($params);

        if (count($errors) > 0) {
            $resp = ['missingField' => $body];
            return $this->jsonResponse($resp, 500);
        }

        $success = $this->login->login($params);

        if ($success) {
            $_POST = array();
            $resp = ['userID' => Session::get(SESSION_USERID), 'privilege' => Session::get(SESSION_PRIVILEGE)];
            return $this->jsonResponse($resp, 200);
        } else {
            $resp = ['success' => false, 'data' => ['INVALID_CREDENTIALS' => true]];
            return $this->setStatusCode(500);
        }
    }

    /**
     * This method handles registering in a user.
     * @param Request $request
     * @return bool|string|null
     */
    public function register(Request $request): bool|string|null
    {
        $body = $request->getBody();

        $params = [
            'first_name' => $body["first_name"],
            'last_name' => $body["last_name"],
            'email' => $body['email'],
            'display_name' => $body['display_name'],
            'password' => $body['password'],
            'password_repeat' => $body['password_repeat'],
        ];

        $errors = $this->register->validate($params);

        if (count($errors) > 0) {
            $errorList = http_build_query(array('error' => $errors));
            return $this->jsonResponse($errorList,500);
        }

        $this->register->register($params);

        return $this->setStatusCode(200);
    }
}
