<?php
namespace App\core\Exceptions;
use \Exception;

class PrivilegeException extends Exception{
  protected $message = 'You do not have the privileges to access this page';
  protected $code = 403;
}
