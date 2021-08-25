<?php
namespace App\Core;

/**
 * Simple response class.
 * @author Viggo Lagestedt Ekholm
 */
class Response
{
  /**
   * Sets the response HTTP code.
   * @param code 
   */
  public function setStatusCode(int $code){
    http_response_code($code);
  }
}
