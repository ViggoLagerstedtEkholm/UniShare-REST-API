<?php
namespace App\Core;
use App\Controllers\Controller;
use \Exception;
use App\Core\Exceptions\ForbiddenException;
use App\Core\Exceptions\PrivilegeException;

/**
 * Application for handling routing/responses/redirects etc.
 * @author Viggo Lagestedt Ekholm
 */
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

  /**
   * Redirect the user to the given path.
   * @param path string path.
   */
  public function redirect($path){
    header('location: ' . $path);
  }

  /**
   * Route to the given URL. If this fails, we either get ForbiddenException (path does not exist)
   * or that the user lack privelages with PrivilegeException.
   */
  public function run(){
    try{
      echo $this->router->resolve();
    }catch(ForbiddenException $e){
      $this->response->setStatusCode(403);
      echo $this->router->renderView('exceptions','unauthorized', [] , ['isError' => true]);
    }catch(PrivilegeException $e){
      $this->response->setStatusCode(401);
      echo $this->router->renderView('exceptions','privilege', [] , ['isError' => true]);
    }
  }

  /**
   * Set the controller.
   * @param controller controller object.
   */
  public function setController(Controller $controller){
    $this->controller = $controller;
  }

  /**
   * Get the controller.
   * @return controller controller object.
   */
  public function getController(){
    return $this->controller;
  }
}
