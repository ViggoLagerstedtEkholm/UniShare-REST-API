<?php

namespace App\core;

/**
 * Cookie helper class for handling setting/getting/checking.
 * @author Viggo Lagestedt Ekholm
 */
class Cookie
{

    /**
     * Set a cookie with the given parameters.
     * @param string $name
     * @param string $value
     * @param int $expiry
     * @return bool
     */
    public static function set(string $name, string $value, int $expiry): bool
    {
        if (setCookie($name, $value, time() + $expiry, '/')) {
            return true;
        }
        return false;
    }

    /**
     * Delete a cookie with the given name.
     * @param string $name
     */
    public static function delete(string $name)
    {
        self::set($name, '', time() - 1);
    }

    /**
     * Get a cookie with the given name.
     * @param string $name
     * @return string
     */
    public static function get(string $name): string
    {
        return $_COOKIE[$name];
    }

    /**
     * Check if a cookie exists with the given name.
     * @param string $name
     * @return bool
     */
    public static function exists(string $name): bool
    {
        return isset($_COOKIE[$name]);
    }
}
