<?php


namespace App\validation;


class ForumValidator
{
    public static function validTitle(string $text): bool
    {
        if (preg_match("/^.{5,50}$/", $text))
        {
            $result = true;
        }else{
            $result = false;
        }
        return $result;
    }

    public static function validTopic(string $text): bool
    {
        if (preg_match("/^.{1,50}$/", $text))
        {
            $result = true;
        }else{
            $result = false;
        }
        return $result;
    }
}