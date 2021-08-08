<?php
namespace App\Core;

class Session
{
  public static function exists($name)
  {
     return (isset($_SESSION[$name])) ? true : false;
  }

  public static function get($name)
  {
    return $_SESSION[$name];
  }

  public static function set($name, $value)
  {
    return $_SESSION[$name] = $value;
  }

  public static function delete($name)
  {
    if(self::exists($name)) {
      unset($_SESSION[$name]);
    }
  }

  public static function isLoggedIn(){
    if(self::exists('userID')){
      return true;
    }else{
      return false;
    }
  }

  public static function deleteAll(){
    session_unset();
    session_destroy();
  }
}

 ?>
