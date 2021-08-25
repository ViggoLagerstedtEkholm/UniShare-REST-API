<?php
namespace App\Core;

/**
 * Cookie helper class for handling setting/getting/checking.
 * @author Viggo Lagestedt Ekholm
 */
class Cookie{

  /**
   * Set a cookie with the given parameters.
   * @param name cookie name.
   * @param value cookie value.
   * @param expiry cookie expiry.
   * @return bool true(success) | false(fail)
   */
  public static function set($name, $value, $expiry) {
    if(setCookie($name, $value, time()+$expiry, '/')) {
      return true;
    }
    return false;
  }

  /**
   * Delete a cookie with the given name.
   * @param name cookie name.
   */
  public static function delete($name) {
    self::set($name, '', time() -1);
  }

  /**
   * Get a cookie with the given name.
   * @param name cookie name.
   * @return Cookie
   */
  public static function get($name) {
    return $_COOKIE[$name];
  }

  /**
   * Check if a cookie exists with the given name.
   * @param name cookie name.
   * @return bool
   */
  public static function exists($name) {
    return isset($_COOKIE[$name]);
  }
}
