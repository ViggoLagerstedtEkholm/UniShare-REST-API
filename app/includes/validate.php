<?php

namespace App\includes;

use JetBrains\PhpStorm\Pure;

/**
 * Validation helper class for input validation.
 * @author Viggo Lagestedt Ekholm
 */
class Validate
{
    /**
     * This method checks if a file has a valid extension and file size.
     * @param $global
     * @return bool
     */
    #[Pure] public static function hasValidUpload($global): bool
    {
        if (Validate::hasInvalidUpload($_FILES[$global]['tmp_name']) !== false) {
            return false;
        }

        $fileSize = $_FILES[$global]['size'];
        $fileErr = $_FILES[$global]['error'];
        $fileType = $_FILES[$global]['type'];

        if (Validate::hasInvalidImageExtension($fileType) !== false) {
            return false;
        }

        //Check if file upload had any errors.
        if ($fileErr === 0) {
            //Enable max file size. 500 000 bytes
            if ($fileSize < MAX_UPLOAD_SIZE) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * This method checks if a file has been uploaded by the user.
     * @param mixed
     * @return bool
     */
    public static function hasInvalidUpload($file): bool
    {
        if (!(file_exists($file)) || !(is_uploaded_file($file))) {
            return true;
        }
        return false;
    }

    /**
     * This method checks if a valid link has been uploaded by the user.
     * @param string $link
     * @return bool
     */
    public static function hasInvalidProjectLink(string $link): bool
    {
        if (filter_var($link, FILTER_VALIDATE_URL) === FALSE) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }

    /**
     * This method checks if a valid image extension has been uploaded by the user.
     * @param string $fileType
     * @return bool
     */
    public static function hasInvalidImageExtension(string $fileType): bool
    {
        $allowed = array("image/jpeg", "image/gif", "image/png");
        if (!in_array($fileType, $allowed)) {
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
        foreach ($array as $v) {
            if ($v == "" || is_null($v)) {
                return true;
            }
        }
        return false;
    }

    /**
     * This method checks if 2 strings mathch.
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
