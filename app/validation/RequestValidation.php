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

    public static function validCode(string $code): bool
    {
        if (preg_match("/^.{1,20}$/", $code)) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }
}