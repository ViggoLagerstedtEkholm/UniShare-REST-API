<?php
namespace App\Core\Exceptions;
use \Exception;

class GDResizeException extends Exception{
  protected $message = 'Failed to resize image for either profile picture or project picture.';
  protected $code = 500;
}
