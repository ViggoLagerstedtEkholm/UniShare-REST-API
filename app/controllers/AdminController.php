<?php
namespace App\Controllers;
use App\Core\Application;
use App\Core\Request;
use App\Includes\Validate;
use App\Models\MVCModels\Users;
use App\Models\MVCModels\Courses;
use App\Core\Session;
use App\Middleware\AuthenticationMiddleware;

class AdminController extends Controller{
  public function __construct(){
    $this->setMiddlewares(new AuthenticationMiddleware(['view', 'updateUser','removeUser', 'addUser', 'addCourse'. 'removeCourse', 'updateCourse'], true));
    $this->users = new Users();
  }

  public function view(){
    $model = new Courses();
    $courses = $model->getCourses();

    if(isset($_GET["filter_option"])){
        $filterOption = $_GET['filter_option'];
    }else{
      $filterOption = "none";
    }

    if(isset($_GET['action'])){
      $filterOrder = $_GET["action"];
    }else{
      $filterOrder = "DESC";
    }

    if(isset($_GET['page'])){
      $page = $_GET['page'];
    }else{
      $page = 1;
    }

    $user_count = $this->users->getUserCount()["COUNT(*)"];
    $results_per_page = 6;
    $number_of_pages = ceil($user_count / $results_per_page);
    $start_page_first_result = ($page-1) * $results_per_page;
    $users = $this->users->getShowcaseUsersPage($start_page_first_result, $results_per_page, $filterOption, $filterOrder);

    if($page > $number_of_pages){
      Application::$app->redirect('./');
    }

    $params = [
      "courses" => $courses,
      'number_of_pages' => $number_of_pages,
      'start_page_first_result' => $start_page_first_result,
      'results_per_page' => $results_per_page,
      'page' => $page
    ];

    return $this->display('admin', 'admin', $params);
  }

  public function updateUser()
  {
    // code...
  }

  public function removeUser()
  {
    // code...
  }

  public function addUser()
  {
    // code...
  }

  public function addCourse()
  {
    // code...
  }

  public function removeCourse()
  {
    // code...
  }

  public function updateCourse()
  {
    // code...
  }
}
