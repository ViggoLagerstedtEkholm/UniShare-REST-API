<?php


namespace App\validation;


class ReviewValidator
{
    public static function validRatings(array $ratings): bool
    {
        foreach($ratings as $value){
            if (!preg_match("/^([1-9]|10)$/", $value)) {
                return false;
            }
        }
        return true;
    }

    public static function validText(string $text): bool
    {
        if (preg_match("/^[\s\S]{200,5000}$/", $text)) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }
}