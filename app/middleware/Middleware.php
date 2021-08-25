<?php
namespace App\Middleware;

/**
 * Authentication middleware for handling controller access.
 * @author Viggo Lagestedt Ekholm
 */
abstract class Middleware{
  abstract public function performCheck();
}
