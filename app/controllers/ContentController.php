<?php
namespace App\Controllers;
use App\Core\Application;
use App\Models\MVCModels\Users;
use App\Models\MVCModels\Courses;
use App\Core\Request;
use App\Core\Session;

class ContentController extends Controller
{
  public function __construct()
  {
    $this->users = new Users();
    $this->courses = new Courses();
  }

  private function getFilters(){
    if(isset($_GET["search"])){
      empty($_GET["search"]) ? $search = null : $search = $_GET["search"];
    }else{
      $search = null;
    }

    if(isset($_GET["filter_option"])){
      empty($_GET["filter_option"]) ? $filterOption = null : $filterOption = $_GET["filter_option"];
    }else{
      $filterOption = null;
    }

    if(isset($_GET['action'])){
      empty($_GET["action"]) ? $filterOrder = null : $filterOrder = $_GET["action"];
    }else{
      $filterOrder = null;
    }

    if(isset($_GET['page'])){
      $page = $_GET['page'];
    }else{
      $page = 1;
    }

    return ['search' => $search, 'filterOption' => $filterOption, 'filterOrder' => $filterOrder, 'page' => $page];
  }

  public function people(){
    $parameters = $this->getFilters();
    $page = $parameters['page'];
    $filterOption = $parameters['filterOption'];
    $filterOrder = $parameters['filterOrder'];
    $search = $parameters['search'];

    if(is_null($search)){
      $user_count = $this->users->getUserCount();
    }
    else{
      $user_count = $this->users->getUserCountSearch($search);
    }

    $offsets = $this->calculateOffsets($user_count, $page);

    $start_page_first_result = $offsets['start_page_first_result'];
    $results_per_page = $offsets['results_per_page'];
    $number_of_pages = $offsets['number_of_pages'];

    if(is_null($search)){
      $users = $this->users->fetchPeopleSearch($start_page_first_result, $results_per_page, $filterOption, $filterOrder);
    }
    else{
      $users = $this->users->fetchPeopleSearch($start_page_first_result, $results_per_page, $filterOption, $filterOrder, $search);
    }

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
    $parameters = $this->getFilters();
    $page = $parameters['page'];
    $filterOption = $parameters['filterOption'];
    $filterOrder = $parameters['filterOrder'];
    $search = $parameters['search'];

    if(is_null($search)){
      $course_count = $this->courses->getCoursesCount();
    }
    else{
      $course_count = $this->courses->getCourseCountSearch($search);
    }

    $offsets = $this->calculateOffsets($course_count, $page);

    $start_page_first_result = $offsets['start_page_first_result'];
    $results_per_page = $offsets['results_per_page'];
    $number_of_pages = $offsets['number_of_pages'];

    if(is_null($search) && empty($search)){
       $courses = $this->courses->fetchCoursesSearch($start_page_first_result, $results_per_page, $filterOption, $filterOrder);
     }
    else{
      $courses = $this->courses->fetchCoursesSearch($start_page_first_result, $results_per_page, $filterOption, $filterOrder, $search);
    }

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

  private function calculateOffsets($count, $page){
    $values = array();
    $results_per_page = 7;
    $number_of_pages = ceil($count / $results_per_page);
    $start_page_first_result = ($page-1) * $results_per_page;

    $values['number_of_pages'] = $number_of_pages;
    $values['results_per_page'] = $results_per_page;
    $values['start_page_first_result'] = $start_page_first_result;
    return $values;
  }

  public function toggleCourseToDegree(Request $request){
    $body = $request->getBody();
    $user = $this->users->getUser(Session::get(SESSION_USERID));

    $degreeID = $user["activeDegreeID"];
    $courseID = $body["courseID"];

    $isInActiveDegree = $this->courses->checkIfCourseExistsInActiveDegree($courseID);

    if($isInActiveDegree){
      $this->courses->deleteDegreeCourse($degreeID, $courseID);
      $resp = ['success'=>true,'data'=>['Status'=>'Deleted']];
      return $this->jsonResponse($resp);
    }else{
      $this->courses->insertDegreeCourse($degreeID, $courseID);
      $resp = ['success'=>true,'data'=>['Status'=>'Inserted']];
      return $this->jsonResponse($resp);
    }
  }
}
