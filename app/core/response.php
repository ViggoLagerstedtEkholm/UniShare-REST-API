<?php

namespace App\core;

/**
 * Simple response class.
 * @author Viggo Lagestedt Ekholm
 */
class Response
{
    function setResponseBody(mixed $resp, string $option): bool|string
    {
        return json_encode($resp, $option);
    }

    function setStatusCode(int $code): bool|int
    {
        return http_response_code($code);
    }
}
