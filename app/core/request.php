<?php

namespace App\core;

use JetBrains\PhpStorm\Pure;

/**
 * Requests handling class.
 * @author Viggo Lagestedt Ekholm
 */
class Request
{
    /**
     * Get the URL path.
     * @return string of the path.
     */
    public function getPath(): string
    {
        $path = str_replace("UniShare/", "", $_SERVER['REQUEST_URI']);
        $position = strpos($path, '?');
        if ($position === false) {
            return $path;
        }

        return substr($path, 0, $position);
    }

    /**
     * Get the HTTP request method.
     * @return string of the method.
     */
    public function getMethod(): string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Make sure we don't get injected code into our database. We loop through
     * both the POST and GET global variables and sanitize them from potential code
     * injections and special characters.
     * @return array
     */
    #[Pure] public function getBody(): array
    {
        $body = [];

        if ($this->getMethod() === 'get') {
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        if ($this->getMethod() === 'post') {
            foreach ($_POST as $key => $value) {
                $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                $body[$key] = strip_tags(html_entity_decode($body[$key]));
            }
        }

        return $body;
    }
}
