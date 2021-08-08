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

    if(is_string($callback)){
      return $this->renderView($callback);
    }

    return call_user_func($callback, $this->request);
  }

  public function renderView($view, $params = [])
  {
    $layoutContent = $this->layoutContent();
    $viewContent = $this->renderOnlyView($view, $params);

    $layoutContent = $this->headerDisplay($layoutContent); //Get the header display.

    return str_replace('---content---', $viewContent, $layoutContent);
  }

  protected function headerDisplay($layoutContent){
    if(Session::exists('userID')){
      $ID = Session::get('userID');
      $headerLinks = "<li><a href='./'>Home</a></li>
                      <li><a href='./profile?ID=$ID'>Profile</a></li>
                      <li><a href='./logout'>Logout</a></li>";

      $layoutContent = str_replace('---navigation---', $headerLinks, $layoutContent);
    }else{
      $headerLinks = "<li><a href='./'>Home</a></li>
                      <li><a href='./login'>Login</a></li>
                      <li><a href='./register'>Register</a></li>";

      $layoutContent = str_replace('---navigation---', $headerLinks, $layoutContent);
    }

    return $layoutContent;
  }

  protected function layoutContent(){
    ob_start();
    include_once Application::$ROOT_DIR . "/UniShare/app/views/html/main.html";
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
