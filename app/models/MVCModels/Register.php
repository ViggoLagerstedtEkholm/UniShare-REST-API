<?php

namespace App\models\MVCModels;

use App\Includes\Validate;

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

        if (Validate::arrayHasEmptyValue($params) === true) {
            $errors[] = INVALID_CREDENTIALS;
        }
        if (Validate::invalidUsername($params["display_name"]) === true) {
            $errors[] = INVALID_USERNAME;
        }
        if (Validate::invalidEmail($params["email"]) === true) {
            $errors[] = INVALID_MAIL;
        }
        if (Validate::match($params["password"], $params["password_repeat"]) === true) {
            $errors[] = INVALID_PASSWORD_MATCH;
        }
        if (!is_null($users->userExists("userEmail", $params["email"]))) {
            $errors[] = EMAIL_TAKEN;
        }
        if (!is_null($users->userExists("userDisplayName", $params["display_name"]))) {
            $errors[] = USERNAME_TAKEN;
        }

        return $errors;
    }

    /**
     * Register user.
     * @param array $params
     */
    function register(array $params)
    {
        $sql = "INSERT INTO users (userFirstName, userLastName, userEmail, userDisplayName, usersPassword, joined) values(?,?,?,?,?,?);";

        $password = $params["password"];
        $hashPassword = password_hash($password, PASSWORD_DEFAULT);
        date_default_timezone_set("Europe/Stockholm");
        $date = date('Y-m-d H:i:s');

        $this->insertOrUpdate($sql, 'ssssss', array($params["first_name"], $params["last_name"], $params["email"], $params["display_name"], $hashPassword, $date));
    }
}
