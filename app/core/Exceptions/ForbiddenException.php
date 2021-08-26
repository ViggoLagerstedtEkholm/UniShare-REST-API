<?php
namespace App\core\Exceptions;
use \Exception;

class ForbiddenException extends Exception{
  protected $message = 'You need to be logged in to do this!';
  protected $code = 403;
}
