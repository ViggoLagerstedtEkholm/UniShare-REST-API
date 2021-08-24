<?php
namespace App\Controllers;
use App\Middleware\Middleware;
use App\Core\Application;
use App\Core\Session;
use App\Core\Request;
use App\Core\ImageHandler;
use App\Includes\Validate;

abstract class Controller{
  public string $action = '';
  protected array $middlewares = [];

  public function setMiddlewares(Middleware $middleware)
  {
      $this->middlewares[] = $middleware;
  }

  public function getMiddlewares(): array
  {
      return $this->middlewares;
  }

  public function display($folder, $page, $params){
    return Application::$app->router->renderView($folder, $page, $params);
  }
  
  protected function calculateOffsets($count, $page, $result_page_count_selected){
    $values = array();
    $results_per_page = $result_page_count_selected;
    $number_of_pages = ceil($count / $results_per_page);
    $start_page_first_result = ($page-1) * $results_per_page;

    $values['number_of_pages'] = $number_of_pages;
    $values['results_per_page'] = $results_per_page;
    $values['start_page_first_result'] = $start_page_first_result;
    return $values;
  }

  protected function jsonResponse($resp, $code){
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: applicaton/json; charset=UTF-8");
    http_response_code($code);
    return json_encode($resp);
  }
}
