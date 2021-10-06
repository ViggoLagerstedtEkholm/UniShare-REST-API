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
        if (preg_match("/^[\s\S]{1,5000}$/", $description))
        {
            $result = true;
        }else{
            $result = false;
        }
        return $result;
    }

    public static function validName (string $name): bool
    {
        if (preg_match("/^[\s\S]{1,300}$/", $name))
        {
            $result = true;
        }else{
            $result = false;
        }
        return $result;
    }

    public static function validURL(string $link): bool
    {
        if (filter_var($link, FILTER_VALIDATE_URL)) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }
}