<?php


namespace App\validation;


class DegreeValidator
{
    public static function validDates(string $start_date, string $end_date): bool
    {
        $start_date_converted = strtotime($start_date);
        $end_date_converted = strtotime($end_date);

        if ($start_date_converted < $end_date_converted) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }

    public static function validFieldOfStudy(string $fieldOfStudy): bool
    {
        if (preg_match("/^(?=.{1,100}$)[a-zA-Z\x{00C0}-\x{00ff}]+(?:[-'\s][a-zA-Z\x{00C0}-\x{00ff}]+)*$/", $fieldOfStudy))
        {
            $result = true;
        }else{
            $result = false;
        }
        return $result;
    }
}