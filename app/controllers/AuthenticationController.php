<?php

namespace App\controllers;

use App\core\Handler;
use App\Core\Session;
use App\Middleware\AuthenticationMiddleware;
use App\Models\Login;
use App\Models\Register;
use App\Models\Users;

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
        return $this->setStatusCode(200);
    }

    /**
     * Get login status.
     * @param Handler $handler
     * @return bool|string
     */
    public function isLoggedIn(Handler $handler): bool|string
    {
        $isLoggedIn = Session::isLoggedIn();
        $resp = ['success' => true, 'data' => ['LoggedIn' => $isLoggedIn]];
        $handler->getResponse()->setStatusCode(200);
        return $handler->getResponse()->setResponseBody($resp);
    }

    /**
     * This method handles logging in a user.
     * @param Handler $handler
     * @return bool|string|null
     */
    public function login(Handler $handler): bool|string|null
    {
        $body = $handler->getRequest()->getBody();

        $params = [
            'email' => $body["email"],
            'password' => $body["password"],
            'rememberMe' => $body['rememberMe']
        ];

        $errors = $this->login->validate($params);

        if (count($errors) > 0) {
            return $handler->getResponse()->jsonResponse($errors, 422);
        }

        $success = $this->login->login($params);

        if ($success) {
            $_POST = array();
            $resp = ['userID' => Session::get(SESSION_USERID), 'privilege' => Session::get(SESSION_PRIVILEGE)];

            return $handler->getResponse()->jsonResponse($resp, 200);
        } else {
            $handler->getResponse()->setStatusCode(500);
        }
        return null;
    }

    /**
     * This method handles registering in a user.
     * @param Handler $handler
     * @return bool|string|null
     */
    public function register(Handler $handler): bool|string|null
    {
        $body = $handler->getRequest()->getBody();

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
            $handler->getResponse()->setStatusCode(422);
            $handler->getResponse()->setResponseBody($errors);
        }

        $this->register->register($params);

        return $handler->getResponse()->setStatusCode(200);
    }
}
