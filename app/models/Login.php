<?php

namespace App\models;

use App\Includes\Validate;
use App\Core\Session;
use App\Core\Cookie;
use JetBrains\PhpStorm\Pure;

/**
 * Model for handling forum queries.
 * @author Viggo Lagestedt Ekholm
 */
class Login extends Database implements IValidate
{
    /**
     * Check if the user input is sufficient enough.
     * @param array $params
     * @return array
     */
    #[Pure] public function validate(array $params): array
    {
        $errors = array();

        if (Validate::arrayHasEmptyValue($params) === true) {
            $errors[] = INVALID_CREDENTIALS;
        }

        return $errors;
    }

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
     * @return bool
     */
    function login(array $params): bool
    {
        $userSession = new UserSession();
        $users = new Users();

        $user = $users->userExists("userEmail", $params['email']);

        if ($user === false) {
            return false;
        }

        $passwordHash = $user["usersPassword"];
        //Compare input attempt password with the stored hashed password.
        $comparePassword = password_verify($params['password'], $passwordHash);

        if ($comparePassword === false) {
            return false;
        } else {
            $ID = $user["usersID"];
            $Email = $user["userEmail"];
            $privilege = $user["privilege"];
            $isSuspended = $user["isSuspended"];

            if($isSuspended == 0){
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
                return true;
            }else{
                return false;
            }
        }
    }
}
