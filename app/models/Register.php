<?php

namespace App\models;

use App\validation\UserValidation;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Model for handling registering users.
 * @author Viggo Lagestedt Ekholm
 */
class Register extends Database implements IValidate
{
    /**
     * Check if the user input is sufficient enough.
     * @param array $params
     * @return array
     */
    public function validate(array $params): array
    {
        $errors = array();
        $users = new Users();

        //First name format check.
        if (!UserValidation::validFirstname($params["first_name"])) {
            $errors[] = INVALID_FIRST_NAME;
        }
        //Last name format check.
        if (!UserValidation::validLastname($params["last_name"])) {
            $errors[] = INVALID_LAST_NAME;
        }
        //Email format check.
        if (!UserValidation::validEmail($params["email"])) {
            $errors[] = INVALID_MAIL;
        }
        //Username format check.
        if (!UserValidation::validUsername($params["display_name"])) {
            $errors[] = INVALID_USERNAME;
        }
        //Password format check.
        if (!(UserValidation::validPassword($params["password"]))){
            $errors[] = INVALID_PASSWORD;
        }
        //Password match check.
        if (!UserValidation::match($params["password"], $params["password_repeat"])) {
            $errors[] = INVALID_PASSWORD_MATCH;
        }
        //Does the registered email exist?
        if (!is_null($users->userExists("userEmail", $params["email"]))) {
            $errors[] = EMAIL_TAKEN;
        }
        //Does the username email exist?
        if (!is_null($users->userExists("userDisplayName", $params["display_name"]))) {
            $errors[] = USERNAME_TAKEN;
        }

        return $errors;
    }

    /**
     * Register user.
     * @param array $params
     * @return array
     */
    #[ArrayShape(['registered' => "bool", 'hash' => "string"])]
    function register(array $params): array
    {
        $sql = "INSERT INTO users (userFirstName, userLastName, userEmail, userDisplayName, usersPassword, joined, verificationHash) values(?,?,?,?,?,?,?);";

        $password = $params["password"];
        $hashPassword = password_hash($password, PASSWORD_DEFAULT);
        date_default_timezone_set("Europe/Stockholm");
        $date = date('Y-m-d H:i:s');
        $hash = md5( rand(0,1000) );
        $registered = $this->insertOrUpdate($sql, 'sssssss', array($params["first_name"], $params["last_name"], $params["email"], $params["display_name"], $hashPassword, $date, $hash));

        return [
            'registered' => $registered,
            'hash' => $hash
        ];
    }
}
