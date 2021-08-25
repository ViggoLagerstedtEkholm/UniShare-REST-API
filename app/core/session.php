<?php
namespace App\Core;

/**
 * Session helper class for handling setting/getting/checking.
 * @author Viggo Lagestedt Ekholm
 */
class Session
{  
  /**
   * Check if a session exists with the given name.
   * @param name session name.
   * @return bool
   */
  public static function exists($name)
  {
     return (isset($_SESSION[$name])) ? true : false;
  }
  
  /**
   * Get a session with the given name.
   * @param name session name.
   * @return Session
   */
  public static function get($name)
  {
    return $_SESSION[$name];
  }

  /**
   * Set a session with the given parameters.
   * @param name session name.
   * @param value session value.
   * @return Session
   */
  public static function set($name, $value)
  {
    return $_SESSION[$name] = $value;
  }
  
  /**
   * Delete a session with the given name.
   * @param name session name.
   */
  public static function delete($name)
  {
    if(self::exists($name)) {
      unset($_SESSION[$name]);
    }
  }
  
  /**
   * Get the client information.
   * @return uagent string
   */
  public static function uagent_no_version() {
    $uagent = $_SERVER['HTTP_USER_AGENT'];
    $regx = '/\/[a-zA-Z0-9.]+/';
    $newString = preg_replace($regx, '', $uagent);
    return $newString;
  }

  /**
   * Check if a user is logged in.
   * @return uagent string
   */
  public static function isLoggedIn(){
    if(self::exists(SESSION_USERID)){
      return true;
    }else{
      return false;
    }
  }

  /**
   * Delete all session data.
   */
  public static function deleteAll(){
    session_unset();
    session_destroy();
  }
}

 ?>
