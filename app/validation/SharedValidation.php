<?php


namespace App\validation;


class SharedValidation
{
    public static function validCountry(string $country): bool
    {
        if (preg_match("/^.{1,56}$/", $country))
        {
            $result = true;
        }else{
            $result = false;
        }
        return $result;
    }

    public static function validCity(string $city): bool
    {
        if (preg_match("/^.{1,120}$/", $city))
        {
            $result = true;
        }else{
            $result = false;
        }
        return $result;
    }

    public static function validUniversity(string $university): bool
    {
        if (preg_match("/^.{1,100}$/", $university))
        {
            $result = true;
        }else{
            $result = false;
        }
        return $result;
    }

    public static function validDescription(string $description): bool
    {
        if (preg_match("/^.{5,2000}$/", $description))
        {
            $result = true;
        }else{
            $result = false;
        }
        return $result;
    }

    public static function validName (string $name): bool
    {
        if (preg_match("/^(?=.{1,150}$)[a-zA-Z\x{00C0}-\x{00ff}]+(?:[-'\s][a-zA-Z\x{00C0}-\x{00ff}]+)*$/", $name))
        {
            $result = true;
        }else{
            $result = false;
        }
        return $result;
    }
}