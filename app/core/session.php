<?php

namespace App\core;

use JetBrains\PhpStorm\Pure;

/**
 * Session helper class for handling setting/getting/checking.
 * @author Viggo Lagestedt Ekholm
 */
class Session
{
    /**
     * Check if a session exists with the given name.
     * @param string $name
     * @return bool
     */
    public static function exists(string $name): bool
    {
        return isset($_SESSION[$name]);
    }

    /**
     * Get a session with the given name.
     * @param string $name
     * @return mixed
     */
    public static function get(string $name): mixed
    {
        return $_SESSION[$name];
    }

    /**
     * Set a session with the given parameters.
     * @param string $name
     * @param string $value
     * @return string
     */
    public static function set(string $name, string $value): string
    {
        return $_SESSION[$name] = $value;
    }

    /**
     * Delete a session with the given name.
     * @param string $name
     */
    public static function delete(string $name)
    {
        if (self::exists($name)) {
            unset($_SESSION[$name]);
        }
    }

    /**
     * Get the client information.
     * @return array|string|null
     */
    public static function agent_no_version(): array|string|null
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        $regx = '/\/[a-zA-Z0-9.]+/';
        return preg_replace($regx, '', $agent);
    }

    /**
     * Check if a user is logged in.
     * @return bool string
     */
    #[Pure] public static function isLoggedIn(): bool
    {
        if (self::exists(SESSION_USERID)) {
            return true;
        } else {
            return false;
        }
    }
}