<?php

namespace App\core;

/**
 * Router class to handle requests and routes.
 * @author Viggo Lagestedt Ekholm
 */
class Router
{
    public Request $request;
    public Response $response;
    protected array $routes = [];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Add route to the routes array. FOR GET
     * @param string $path
     * @param $callback
     */
    public function get(string $path, $callback)
    {
        $this->routes['get'][$path] = $callback;
    }

    /**
     * Add route to the routes array. FOR POST
     * @param $path
     * @param $callback
     */
    public function post($path, $callback)
    {
        $this->routes['post'][$path] = $callback;
    }

    /**
     * This method resolves the request and makes sure we run the middleware
     * before the user can access the requested URL. The call to call_user_func calls the
     * action method in the responsible controller.
     * @return mixed
     */
    public function resolve(): mixed
    {
        $path = $this->request->getPath();
        $method = $this->request->getMethod();
        $callback = $this->routes[$method][$path] ?? false;

        if ($callback === false) {
            $this->response->setStatusCode(404);
            return "This path does not exist!";
        }

        if (is_array($callback)) {
            $controller = new $callback[0]();
            $controller->action = $callback[1];
            $callback[0] = $controller;
            Application::$app->setController($controller);

            foreach (Application::$app->getController()->getMiddlewares() as $middleware) {
                $middleware->performCheck();
            }
        }

        if (is_string($callback)) {
            return $this->renderView($callback, null);
        }

        return call_user_func($callback, $this->request);
    }

    /**
     * This method is what actually draws the page to the user. We get the parameters
     * and file path information to fetch the required files and finally return the view.
     * We use placeholders to replace the header/footer/content. This is efficient because
     * that allows us to have header and footer content in separate files to display on every page.
     * @param $folder
     * @param $view
     * @param array $params
     * @param false[] $errorParams
     * @return array|string
     */
    public function renderView($folder, $view, array $params = [], array $errorParams = ['isError' => false]): array|string
    {
        $layoutContent = $this->layoutContent();
        $viewContent = $this->renderOnlyView($folder, $view, $params);
        $header = $this->renderOnlyView('layout', 'header', $errorParams);
        $footer = $this->renderOnlyView('layout', 'footer', $errorParams);

        $temp = str_replace('---header---', $header, $layoutContent);
        $temp = str_replace('---footer---', $footer, $temp);
        return str_replace('---content---', $viewContent, $temp);
    }

    /**
     * Get the main layout with the HTML/HEAD/BODY tags.
     * @return false|string
     */
    protected function layoutContent(): bool|string
    {
        ob_start();
        include_once Application::$ROOT_DIR . "/UniShare/app/views/layout/Main.html";
        return ob_get_clean();
    }

    /**
     * Go though the parameter array and make use of the $$ variable, this allows us to
     * access all associate array key name variables in the view file.
     * @param $folder
     * @param $view
     * @param $params
     * @return false|string
     */
    protected function renderOnlyView($folder, $view, $params): bool|string
    {
        foreach ($params as $key => $value) {
            $$key = $value;
        }

        ob_start();
        include_once Application::$ROOT_DIR . "/UniShare/app/views/$folder/$view.php";
        return ob_get_clean();
    }
}
