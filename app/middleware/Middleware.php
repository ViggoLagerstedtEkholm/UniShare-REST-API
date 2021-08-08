<?php
namespace App\Middleware;

abstract class Middleware{
  abstract public function performCheck();
}
