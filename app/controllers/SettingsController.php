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
        $this->setMiddlewares(new AuthenticationMiddleware(['deleteAccount', 'getSettings', 'update']));

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

        if (!empty($updated_current_password) && !empty($updated_new_password)) {
            $comparePassword = password_verify($updated_current_password, $passwordHash);

            echo $comparePassword;

            if ($comparePassword === false) {
                $errors[] = INVALID_PASSWORD_MATCH;
            } else {
                $hashPassword = password_hash($updated_new_password, PASSWORD_DEFAULT);
                $this->users->updateUser("usersPassword", $hashPassword, $ID);
            }
        }

        if (!$this->degrees->userHasDegreeID($updated_activeDegreeID)) {
            $errors[] = INVALID_ACTIVEDEGREEID;
        }
        if (Validate::invalidUsername($updated_display_name) === true) {
            $errors[] = INVALID_USERNAME;
        }
        if (!is_null($this->users->userExists( "userEmail", $updated_email)) && $updated_email != $email) {
            $errors[] = EMAIL_TAKEN;
        }
        if (!is_null($this->users->userExists("userDisplayName", $updated_display_name))) {
            $errors[] = INVALID_USERNAME;
        }

        if (count($errors) > 0) {
            return $handler->getResponse()->jsonResponse($errors, 500);
        }

        if ($updated_description != $description) {
            $this->users->updateUser("description", $updated_description, $ID);
        }
        if ($updated_activeDegreeID != $activeDegreeID) {
            $this->users->updateUser("activeDegreeID", $updated_activeDegreeID, $ID);
        }
        if ($updated_first_name != $first_name) {
            $this->users->updateUser("userFirstName", $updated_first_name, $ID);
        }
        if ($updated_last_name != $last_name) {
            $this->users->updateUser("userLastName", $updated_last_name, $ID);
        }
        if ($updated_email != $email) {
            $this->users->updateUser("userEmail", $updated_email, $ID);
        }
        if ($updated_display_name != $display_name) {
            $this->users->updateUser("userDisplayName", $updated_display_name, $ID);
        }

        $handler->getResponse()->setStatusCode( 200);
        return null;
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

        $resp = ['success' => true, 'data' => ['email' => $email, 'first_name' => $first_name, 'last_name' => $last_name,
                'display_name' => $display_name, 'description' => $description]];

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
