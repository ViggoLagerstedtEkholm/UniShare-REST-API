<?php


namespace App\validation;


class PostValidator
{
    public static function validPost(string $text): bool
    {
        if (preg_match("/^[\s\S]{5,500}$/", $text))
        {
            $result = true;
        }else{
            $result = false;
        }
        return $result;
    }
}