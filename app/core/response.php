<?php

namespace App\core;

/**
 * Simple response class.
 * @author Viggo Lagestedt Ekholm
 */
class Response
{
    /**
     * Sets the response HTTP code.
     * @param int $code
     */
    public function setStatusCode(int $code)
    {
        http_response_code($code);
    }
}
