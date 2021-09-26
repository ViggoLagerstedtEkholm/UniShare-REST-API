<?php

namespace App\includes;

/**
 * Validation helper class for input validation.
 * @author Viggo Lagestedt Ekholm
 */
class Validate
{
    /**
     * This method checks if a valid link has been uploaded by the user.
     * @param string $link
     * @return bool
     */
    public static function hasValidURL(string $link): bool
    {
        if (filter_var($link, FILTER_VALIDATE_URL)) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }

    /**
     * This method checks if 2 valid dates has been uploaded by the user.
     * @param string $start_date
     * @param string $end_date
     * @return bool
     */
    public static function hasInvalidDates(string $start_date, string $end_date): bool
    {
        $start_date_converted = strtotime($start_date);
        $end_date_converted = strtotime($end_date);

        if ($start_date_converted > $end_date_converted) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }

    /**
     * This method checks if a valid username has been uploaded by the user.
     * @param string $username
     * @return bool
     */
    public static function invalidUsername(string $username): bool
    {
        if (!preg_match("/^[a-zA-Z0-9]*$/", $username)) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }

    /**
     * This method checks if a valid email has been uploaded by the user.
     * @param string $email
     * @return bool
     */
    public static function invalidEmail(string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }

    /**
     * This method checks if a array has empty key => value pair.
     * @param array $array
     * @return bool
     */
    public static function arrayHasEmptyValue(array $array): bool
    {
        foreach ($array as $value) {
            if (is_null($value) || $value === "") {
                return true;
            }
        }
        return false;
    }

    /**
     * This method checks if 2 strings match.
     * @param string $password
     * @param string $password_repeat
     * @return bool
     */
    public static function match(string $password, string $password_repeat): bool
    {
        if ($password !== $password_repeat) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }
}
