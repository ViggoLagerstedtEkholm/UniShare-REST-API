<?php
namespace App\Core;

class Router{
  public Request $request;
  public Response $response;
  protected array $routes = [];

  public function __construct(Request $request, Response $response){
    $this->request = $request;
    $this->response = $response;
  }

  public function get($path, $callback){
      $this->routes['get'][$path] = $callback;
  }

  public function post($path, $callback){
      $this->routes['post'][$path] = $callback;
  }

  public function resolve(){
    $path = $this->request->getPath();
    $method = $this->request->getMethod();
    $callback = $this->routes[$method][$path] ?? false;

    if($callback === false){
      $this->response->setStatusCode(404);
      return "Not found";
      exit;
    }

    if(is_array($callback)){
      $controller = new $callback[0]();
      $controller->action = $callback[1];
      $callback[0] = $controller;
      Application::$app->setController($controller);

      foreach (Application::$app->getController()->getMiddlewares() as $middleware) {
          $middleware->performCheck();
      }
    }

    if(is_string($callback)){
      return $this->renderView($callback);
    }

    return call_user_func($callback, $this->request);
  }

  public function renderView($view, $params = [], $errorParams = ['isError' => false])
  {
    $layoutContent = $this->layoutContent();
    $viewContent = $this->renderOnlyView($view, $params);
    $header = $this->renderOnlyView('header', $errorParams);

    $temp;
    $temp = str_replace('---header---', $header, $layoutContent);
    $temp = str_replace('---content---', $viewContent, $temp);
    return $temp;
  }

  protected function layoutContent(){
    ob_start();
    include_once Application::$ROOT_DIR . "/UniShare/app/views/html/Main.html";
    return ob_get_clean();
  }

  protected function renderOnlyView($view, $params){
    foreach($params as $key => $value){
      $$key = $value;
    }

    ob_start();
    include_once Application::$ROOT_DIR . "/UniShare/app/views/view/$view.php";
    return ob_get_clean();
  }
}
