<?php

namespace App\core;

/**
 * Simple response class.
 * @author Viggo Lagestedt Ekholm
 */
class Response
{
    function jsonResponse(mixed $resp, int $code, int $option = JSON_PARTIAL_OUTPUT_ON_ERROR): bool|string|null
    {
        $this->setStatusCode($code);

        if(!is_null($resp)){
            return $this->setResponseBody($resp, $option);
        }else{
            return null;
        }
    }

    function setResponseBody(mixed $resp, int $option = JSON_PARTIAL_OUTPUT_ON_ERROR): bool|string
    {
        return json_encode($resp, $option);
    }

    function setStatusCode(int $code): bool|int
    {
        return http_response_code($code);
    }
}
