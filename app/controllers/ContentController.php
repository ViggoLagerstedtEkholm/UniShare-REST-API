<?php
namespace App\Controllers;
use App\Core\Application;
use App\Models\MVCModels\Users;
use App\Models\MVCModels\Profiles;
use App\Models\MVCModels\Projects;
use App\Models\MVCModels\Courses;
use App\Core\ImageHandler;

class ContentController extends Controller
{
  public function __construct()
  {
    $this->imageHandler = new ImageHandler();
    $this->users = new Users();
    $this->courses = new Courses();
    $this->projects = new Projects();
  }

  public function people()
  {
    if(isset($_GET["search"])){
      $search = $_GET["search"];
    }else{
      $search = "";
    }

    if(isset($_GET["filter_option"])){
        $filterOption = $_GET['filter_option'];
    }else{
      $filterOption = "userDisplayName";
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

    if($search == ""){$user_count = $this->users->getUserCount();}
    else{$user_count = $this->users->getUserCountSearch($search);}
    $results_per_page = 7;
    $number_of_pages = ceil($user_count / $results_per_page);
    $start_page_first_result = ($page-1) * $results_per_page;

    if($search == ""){ $users = $this->users->fetchPeopleSearch($start_page_first_result, $results_per_page, $filterOption, $filterOrder);}
    else{$users = $this->users->fetchPeopleSearch($start_page_first_result, $results_per_page, $filterOption, $filterOrder, $search);}

    if($page > $number_of_pages){
      Application::$app->redirect('./searchPeople?error=nomatchesfound');
    }

    $params = [
      'users' => $users,
      'page' => $page,
      'filterOption' => $filterOption,
      'filterOrder' => $filterOrder,
      'number_of_pages' => $number_of_pages,
      'start_page_first_result' => $start_page_first_result,
      'results_per_page' => $results_per_page,
      'search' => $search
    ];

    return $this->display('content/people', 'people', $params);
  }

  public function courses(){
    if(isset($_GET["search"])){
      $search = $_GET["search"];
    }else{
      $search = "";
    }

    if(isset($_GET["filter_option"])){
        $filterOption = $_GET['filter_option'];
    }else{
      $filterOption = "name";
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

    if($search == ""){$course_count = $this->courses->getCoursesCount();}
    else{$course_count = $this->courses->getCourseCountSearch($search);}
    $results_per_page = 7;
    $number_of_pages = ceil($course_count / $results_per_page);
    $start_page_first_result = ($page-1) * $results_per_page;

    if($search == ""){ $courses = $this->courses->fetchCoursesSearch($start_page_first_result, $results_per_page, $filterOption, $filterOrder);}
    else{$courses = $this->courses->fetchCoursesSearch($start_page_first_result, $results_per_page, $filterOption, $filterOrder, $search);}

    if($page > $number_of_pages){
      Application::$app->redirect('./searchCourses?error=nomatchesfound');
    }

    $params = [
      'courses' => $courses,
      'page' => $page,
      'filterOption' => $filterOption,
      'filterOrder' => $filterOrder,
      'number_of_pages' => $number_of_pages,
      'start_page_first_result' => $start_page_first_result,
      'results_per_page' => $results_per_page,
      'search' => $search
    ];

    return $this->display('content/courses', 'courses', $params);
  }
}
?>
