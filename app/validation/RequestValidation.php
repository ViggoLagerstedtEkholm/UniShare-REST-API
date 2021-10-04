<?php


namespace App\validation;


class RequestValidation
{
    public static function validCredits(string $credits): bool
    {
        if (preg_match("/^[+]?([0-9]+\.?[0-9]*|\.[0-9]+)$/", $credits)) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }
}