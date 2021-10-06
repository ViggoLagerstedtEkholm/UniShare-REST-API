<?php

namespace App\controllers;

use App\core\Handler;
use App\Core\Session;
use App\validation\SharedValidation;
use App\validation\UserValidation;
use App\Middleware\AuthenticationMiddleware;
use App\Models\Users;

/**
 * Settings controller for handling user settings.
 * @author Viggo Lagestedt Ekholm
 */
class SettingsController extends Controller
{
    private Users $users;

    function __construct()
    {
        $this->setMiddlewares(new AuthenticationMiddleware([
            'deleteAccount',
            'getSettings',
            'update',
            'fetch',
            'updatePassword',
            'checkEmailAvailability',
            'checkUsernameAvailability',
            'getHandles',
            'deleteLinkedIn',
            'deleteGitHub',
            'updateHandles']));

        $this->users = new Users();
    }

    /**
     * This method handles updating the settings and validating inputs.
     * @param Handler $handler
     * @return bool|string|null
     */
    public function updateAccount(Handler $handler): bool|string|null
    {
        $updatedInfo = $handler->getRequest()->getBody();
        $currentUserID = Session::get(SESSION_USERID);
        //New settings
        $updated_first_name = $updatedInfo["first_name"];
        $updated_last_name = $updatedInfo["last_name"];
        $updated_email = $updatedInfo["email"];
        $updated_display_name = $updatedInfo["display_name"];
        $updated_description = $updatedInfo["description"];

        $user = $this->users->getUser(Session::get(SESSION_USERID));
        //Current settings
        $first_name = $user["userFirstName"];
        $last_name = $user["userLastName"];
        $email = $user["userEmail"];
        $display_name = $user["userDisplayName"];
        $description = $user["description"];

        $errors = array();

        //Check existing users information.
        if ($updated_email !== $email && !is_null($this->users->userExists( "userEmail", $updated_email))) {
            $errors[] = EMAIL_TAKEN;
        }
        if ($updated_display_name !== $display_name && !is_null($this->users->userExists("userDisplayName", $updated_display_name))) {
            $errors[] = USERNAME_TAKEN;
        }

        if (!UserValidation::validEmail($updated_email)) {
            $errors[] = INVALID_EMAIL;
        }
        if (!UserValidation::validFirstname($updated_first_name)) {
            $errors[] = INVALID_FIRST_NAME;
        }
        if (!UserValidation::validLastname($updated_last_name)) {
            $errors[] = INVALID_LAST_NAME;
        }
        if (!UserValidation::validUsername($updated_display_name)) {
            $errors[] = INVALID_USERNAME;
        }
        if(!UserValidation::validDescription($description)){
            $errors[] = INVALID_DESCRIPTION;
        }

        if (count($errors) > 0) {
            return $handler->getResponse()->jsonResponse($errors, 500);
        }

        if ($updated_description != $description) {
            $this->users->updateUser("description", $updated_description, $currentUserID);
        }
        if ($updated_first_name != $first_name) {
            $this->users->updateUser("userFirstName", $updated_first_name, $currentUserID);
        }
        if ($updated_last_name != $last_name) {
            $this->users->updateUser("userLastName", $updated_last_name, $currentUserID);
        }
        if ($updated_email != $email) {
            //$this->users->updateUser("userEmail", $updated_email, $currentUserID);
        }
        if ($updated_display_name != $display_name) {
            $this->users->updateUser("userDisplayName", $updated_display_name, $currentUserID);
        }

        return $handler->getResponse()->jsonResponse( $updated_description, 200);
    }

    public function checkUsernameAvailability(Handler $handler): bool|string|null
    {
        $body = $handler->getRequest()->getBody();
        $newUsername = $body['display_name'];

        $user = $this->users->getUser(Session::get(SESSION_USERID));
        //Current settings
        $currentUsername = $user["userDisplayName"];

        if ($newUsername !== $currentUsername && !is_null($this->users->userExists( "userDisplayName", $newUsername))) {
            return $handler->getResponse()->jsonResponse(false, 200);
        }else{
            return $handler->getResponse()->jsonResponse(true, 200);
        }
    }

    public function checkEmailAvailability(Handler $handler): bool|string|null
    {
        $body = $handler->getRequest()->getBody();
        $newEmail = $body['email'];

        $user = $this->users->getUser(Session::get(SESSION_USERID));
        //Current settings
        $currentEmail = $user["userEmail"];

        if ($newEmail !== $currentEmail && !is_null($this->users->userExists( "userEmail", $newEmail))) {
            return $handler->getResponse()->jsonResponse(false, 200);
        }else{
            return $handler->getResponse()->jsonResponse(true, 200);
        }
    }

    public function updatePassword(Handler $handler): bool|int|string
    {
        $updatedInfo = $handler->getRequest()->getBody();
        $current_password = $updatedInfo["current_password"];
        $new_password = $updatedInfo["new_password"];

        $user = $this->users->getUser(Session::get(SESSION_USERID));
        $passwordHash = $user["usersPassword"];

        if(!UserValidation::validPassword($new_password)){
            return $handler->getResponse()->jsonResponse(INVALID_PASSWORD, 422);
        }

        $comparePassword = password_verify($current_password, $passwordHash);

        if ($comparePassword === false) {
            return $handler->getResponse()->jsonResponse(INVALID_CREDENTIALS, 422);
        } else {
            $hashedNewPassword = password_hash($new_password, PASSWORD_DEFAULT);
            $this->users->updateUser("usersPassword", $hashedNewPassword, Session::get(SESSION_USERID));
            return $handler->getResponse()->setStatusCode( 200);
        }
    }

    public function updateActiveDegree(Handler $handler){
        $updatedDegree = $handler->getRequest()->getBody();
        $newActiveDegreeID = $updatedDegree["activeDegreeID"];

        $user = $this->users->getUser(Session::get(SESSION_USERID));
        $currentActiveDegreeID = $user["activeDegreeID"];

        if ($newActiveDegreeID != $currentActiveDegreeID && !empty($newActiveDegreeID)) {
            $this->users->updateUser("activeDegreeID", $newActiveDegreeID, Session::get(SESSION_USERID));
        }

        $handler->getResponse()->setStatusCode(200);
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

    public function deleteLinkedIn(){
        $userID = Session::get(SESSION_USERID);
        $this->users->deleteLinkedIn($userID);
    }

    public function deleteGitHub(){
        $userID = Session::get(SESSION_USERID);
        $this->users->deleteGitHub($userID);
    }

    public function updateHandles(Handler $handler): bool|int|string|null
    {
        $body = $handler->getRequest()->getBody();
        $linkedIn = $body['linkedin'];
        $github = $body['github'];
        $userID = Session::get(SESSION_USERID);

        if(!empty($linkedIn) && !SharedValidation::validURL($linkedIn)){
            return $handler->getResponse()->jsonResponse(INVALID_LINK, 422);
        }

        if(!empty($linkedIn)){
            $this->users->addLinkedIn($linkedIn, $userID);
        }

        if(!empty($github) && !SharedValidation::validURL($github)){
            return $handler->getResponse()->jsonResponse(INVALID_LINK, 422);
        }

        if(!empty($github)){
            $this->users->addGitHub($github, $userID);
        }

        return $handler->getResponse()->setStatusCode(200);
    }

    public function getHandles(Handler $handler): bool|string|null
    {
        $user = $this->users->getUser(Session::get(SESSION_USERID));
        $linkedIn = $user["linkedin"];
        $github = $user["github"];

        $resp =[
            'github' => $github,
            'linkedin' => $linkedIn
        ];

        return $handler->getResponse()->jsonResponse($resp, 200);
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

        $resp = ['email' => $email, 'first_name' => $first_name, 'last_name' => $last_name,
            'display_name' => $display_name, 'description' => $description];

        return $handler->getResponse()->jsonResponse($resp, 200);
    }
}
