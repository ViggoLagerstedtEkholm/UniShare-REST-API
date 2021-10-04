<?php

namespace App\models;

use App\Core\Session;
use App\Core\Cookie;

/**
 * Model for handling forum queries.
 * @author Viggo Lagestedt Ekholm
 */
class Login extends Database
{
    /**
     * Login using the user's cookie.
     */
    function loginFromCOOKIE()
    {
        $userSession = new UserSession();
        $users = new Users();

        $userSession = $userSession->getSessionFromCookie();
        $ID = $userSession['userID'];
        $session = $userSession['session'];

        if ($session != '' && $ID != '' && !empty($userSession)) {
            $user = $users->getUser($ID);
            $email = $user["userEmail"];
            $privilege = $user["privilege"];

            Session::set(SESSION_USERID, $ID);
            Session::set(SESSION_MAIL, $email);
            Session::set(SESSION_PRIVILEGE, $privilege);
        }
    }

    /**
     * Login user.
     * @param array $params
     * @return array|false
     */
    function login(array $params): array|false
    {
        $userSession = new UserSession();
        $users = new Users();

        $user = $users->userExists("userEmail", $params['email']);

        if ($user === null) {
            return [
                'ERRORS' => [INVALID_CREDENTIALS],
                'success' => false
            ];
        }

        $passwordHash = $user["usersPassword"];
        //Compare input attempt password with the stored hashed password.
        $comparePassword = password_verify($params['password'], $passwordHash);

        if ($comparePassword === false) {
            return [
                'ERRORS' => [INVALID_CREDENTIALS],
                'success' => false
            ];
        } else {
            $errors = array();

            $ID = $user["usersID"];
            $Email = $user["userEmail"];
            $privilege = $user["privilege"];
            $isSuspended = $user["isSuspended"];
            $isVerified = $user["isVerified"];

            if($isSuspended == 1){
                $errors[] = "SUSPENDED";
            }

            if($isVerified == 0){
                $errors[] = "NOT_VERIFIED";
            }

            if($isSuspended == 0 && $isVerified == 1){
                Session::set(SESSION_USERID, $ID);
                Session::set(SESSION_MAIL, $Email);
                Session::set(SESSION_PRIVILEGE, $privilege);

                if ($params['rememberMe'] == "true") {
                    $hash = md5(uniqid(rand(), true));
                    $user_agent = Session::agent_no_version();
                    Cookie::set(REMEMBER_ME_COOKIE_NAME, $hash, REMEMBER_ME_COOKIE_EXPIRY);
                    $userSession->deleteExistingSession($ID, $user_agent); //If any previous session exists, remove.
                    $userSession->insertSession($ID, $user_agent, $hash); //Insert the new session ID.
                }
                return [
                    'ERRORS' => $errors,
                    'success' => true
                ];
            }else{
                return [
                    'ERRORS' => $errors,
                    'success' => false
                ];
            }
        }
    }
}
