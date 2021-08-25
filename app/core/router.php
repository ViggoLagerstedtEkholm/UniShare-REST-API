<?php
namespace App\Core;

/**
 * Router class to handle requests and routes.
 * @author Viggo Lagestedt Ekholm
 */
class Router{
  public Request $request;
  public Response $response;
  protected array $routes = [];

  public function __construct(Request $request, Response $response){
    $this->request = $request;
    $this->response = $response;
  }

  /**
   * Add route to the routes array. FOR GET
   * @param path URL example /profile, /home etc...
   * @param callback the controller and controller method that should handle the request.
   */
  public function get($path, $callback){
      $this->routes['get'][$path] = $callback;
  }

  /**
   * Add route to the routes array. FOR POST
   * @param path URL example /profile, /home etc...
   * @param callback the controller and controller method that should handle the request.
   */
  public function post($path, $callback){
      $this->routes['post'][$path] = $callback;
  }

  /**
   * This method resolves the request and makes sure we run the middleware
   * before the user can access the requested URL. The call to call_user_func calls the
   * action method in the responsible controller.
   * @return call_user_func return value of the callback.
   */
  public function resolve(){
    $path = $this->request->getPath();
    $method = $this->request->getMethod();
    $callback = $this->routes[$method][$path] ?? false;

    if($callback === false){
      $this->response->setStatusCode(404);
      return "This path does not exist!";
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

  /**
   * This method is what actually draws the page to the user. We get the parameters
   * and file path information to fetch the required files and finally return the view.
   * We use placeholders to replace the header/footer/content. This is efficient because
   * that allows us to have header and footer content in seperate files to display on every page.
   * @param folder folder name of the view.
   * @param view file name of the view.
   * @param params associate array of parameters to show the user.
   * @param errorParams indicates if a error has occured.
   * @return View of the requested page.
   */
  public function renderView($folder, $view, $params = [], $errorParams = ['isError' => false])
  {
    $layoutContent = $this->layoutContent();
    $viewContent = $this->renderOnlyView($folder, $view, $params);
    $header = $this->renderOnlyView('layout','header', $errorParams);
    $footer = $this->renderOnlyView('layout','footer', $errorParams);

    $temp = str_replace('---header---', $header, $layoutContent);
    $temp = str_replace('---footer---', $footer, $temp);
    $temp = str_replace('---content---', $viewContent, $temp);
    return $temp;
  }

  /**
   * Get the main layout with the HTML/HEAD/BODY tags.
   * @return View of the layout page.
   */
  protected function layoutContent(){
    ob_start();
    include_once Application::$ROOT_DIR . "/UniShare/app/views/layout/Main.html";
    return ob_get_clean();
  }

  /**
   * Go though the parameter array and make use of the $$ variable, this allows us to
   * access all associate array key name variables in the view file.
   * @return View of the main content page.
   */
  protected function renderOnlyView($folder, $view, $params){
    foreach($params as $key => $value){
      $$key = $value;
    }

    ob_start();
    include_once Application::$ROOT_DIR . "/UniShare/app/views/$folder/$view.php";
    return ob_get_clean();
  }
}
