<?php

namespace App\controllers;

use App\core\Handler;
use App\Core\Session;
use App\Includes\Validate;
use App\Middleware\AuthenticationMiddleware;
use App\Models\Degrees;
use App\Models\Users;

/**
 * Settings controller for handling user settings.
 * @author Viggo Lagestedt Ekholm
 */
class SettingsController extends Controller
{
    private Users $users;
    private Degrees $degrees;

    function __construct()
    {
        $this->setMiddlewares(new AuthenticationMiddleware(['view', 'deleteAccount', 'getSettings', 'update']));

        $this->users = new Users();
        $this->degrees = new Degrees();
    }

    /**
     * This method handles updating the settings and validating inputs.
     * @param Handler $handler
     * @return bool|string|null
     */
    public function update(Handler $handler): bool|string|null
    {
        $fields = ["userFirstName", "userLastName", "userEmail", "userDisplayName", "usersPassword", "activeDegreeID", "description"];

        $updatedInfo = $handler->getRequest()->getBody();

        $updated_first_name = $updatedInfo["first_name"];
        $updated_last_name = $updatedInfo["last_name"];
        $updated_email = $updatedInfo["email"];
        $updated_display_name = $updatedInfo["display_name"];
        $updated_current_password = $updatedInfo["current_password"];
        $updated_new_password = $updatedInfo["new_password"];
        $updated_activeDegreeID = $updatedInfo["activeDegreeID"];
        $updated_description = $updatedInfo["description"];

        $user = $this->users->getUser(Session::get(SESSION_USERID));
        $ID = $user["usersID"];
        $first_name = $user["userFirstName"];
        $last_name = $user["userLastName"];
        $email = $user["userEmail"];
        $display_name = $user["userDisplayName"];
        $passwordHash = $user["usersPassword"];
        $activeDegreeID = $user["activeDegreeID"];
        $description = $user["description"];

        $errors = array();

        if (!$this->degrees->userHasDegreeID($updated_activeDegreeID)) {
            $errors[] = INVALID_ACTIVEDEGREEID;
        }

        if (!empty($updated_current_password) && !empty($updated_new_password)) {
            $comparePassword = password_verify($updated_current_password, $passwordHash);

            if ($comparePassword === false) {
                $errors[] = INVALID_PASSWORD_MATCH;
            } else {
                $hashPassword = password_hash($updated_new_password, PASSWORD_DEFAULT);
                $this->users->updateUser($fields[4], $hashPassword, $ID);
            }
        }

        if (Validate::invalidUsername($updated_display_name) === true) {
            $errors[] = INVALID_USERNAME;
        }
        if (!is_null($this->users->userExists($fields[2], $updated_email)) && $updated_email != $email) {
            $errors[] = EMAIL_TAKEN;
        }
        if (!is_null($this->users->userExists($fields[3], $updated_email)) && $updated_display_name != $display_name) {
            $errors[] = INVALID_USERNAME;
        }

        if (count($errors) > 0) {
            $errorList = http_build_query(array('error' => $errors));
            return $handler->getResponse()->jsonResponse($errorList, 500);
        }

        if ($updated_description != $description) {
            $this->users->updateUser($fields[6], $updated_description, $ID);
        }
        if ($updated_activeDegreeID != $activeDegreeID) {
            $this->users->updateUser($fields[5], $updated_activeDegreeID, $ID);
        }
        if ($updated_first_name != $first_name) {
            $this->users->updateUser($fields[0], $updated_first_name, $ID);
        }
        if ($updated_last_name != $last_name) {
            $this->users->updateUser($fields[1], $updated_last_name, $ID);
        }
        if ($updated_email != $email) {
            $this->users->updateUser($fields[2], $updated_email, $ID);
        }
        if ($updated_display_name != $display_name) {
            $this->users->updateUser($fields[3], $updated_display_name, $ID);
        }

        return $handler->getResponse()->jsonResponse(true, 200);
    }

    /**
     * Get the current settings and return the fields.
     * @return false|string
     */
    public function fetch(Handler $handler): bool|string
    {
        $user = $this->users->getUser(Session::get(SESSION_USERID));
        $first_name = $user["userFirstName"];
        $last_name = $user["userLastName"];
        $email = $user["userEmail"];
        $display_name = $user["userDisplayName"];
        $description = $user["description"];

        $resp = ['success' => true, 'data' => ['email' => $email, 'first_name' => $first_name, 'last_name' => $last_name, 'display_name' => $display_name, 'description' => $description]];

        return $handler->getResponse()->jsonResponse($resp, 200);
    }

    /**
     * Delete the user account.
     */
    public function deleteAccount(Handler $handler)
    {
        $userID = Session::get(SESSION_USERID);

        $this->users->terminateAccount($userID);
        $this->users->logout();
        $handler->getResponse()->setStatusCode(200);
    }
}
