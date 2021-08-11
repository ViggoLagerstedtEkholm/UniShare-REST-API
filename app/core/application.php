<?php
namespace App\Core;
use App\Controllers\Controller;
use \Exception;

class Application{
  public static string $ROOT_DIR;
  public Router $router;
  public Request $request;
  public Response $response;
  public static Application $app;
  private ?Controller $controller = null;

  public function __construct($rootPath){
    self::$ROOT_DIR = $rootPath;
    self::$app = $this;

    $this->request = new Request();
    $this->response = new Response();
    $this->router = new Router($this->request, $this->response);
  }

  public function redirect($path){
    header('location: ' . $path);
  }

  public function run(){
    try{
      echo $this->router->resolve();
    }catch(Exception $e){
      echo $this->router->renderView('exceptions','unauthorized', [] , ['isError' => true]);
    }
  }

  public function setController(Controller $controller){
    $this->controller = $controller;
  }

  public function getController(){
    return $this->controller;
  }
}
