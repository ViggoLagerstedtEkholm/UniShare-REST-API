<?php
namespace App\Controllers;
use App\Core\Application;
use App\Models\MVCModels\Users;
use App\Models\MVCModels\Courses;
use App\Models\MVCModels\Forums;
use App\Core\Request;
use App\Core\Session;

class ContentController extends Controller
{
  private $users;
  private $courses;
  private $forums;

  public function __construct()
  {
    $this->users = new Users();
    $this->courses = new Courses();
    $this->forums = new Forums();
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

    if(isset($_GET['results_per_page_count'])){
      empty($_GET["results_per_page_count"]) ? $results_per_page_count = null : $results_per_page_count = $_GET["results_per_page_count"];
    }else{
      $results_per_page_count = 7;
    }

    return ['search' => $search, 'filterOption' => $filterOption, 'filterOrder' => $filterOrder, 'page' => $page, 'results_per_page_count' => $results_per_page_count];
  }

  public function people(){
    $parameters = $this->getFilters();
    $page = $parameters['page'];
    $filterOption = $parameters['filterOption'];
    $filterOrder = $parameters['filterOrder'];
    $search = $parameters['search'];
    $results_per_page_count = $parameters['results_per_page_count'];

    if(is_null($search)){
      $user_count = $this->users->getUserCount();
    }
    else{
      $user_count = $this->users->getUserCountSearch($search);
    }

    $offsets = $this->calculateOffsets($user_count, $page, $results_per_page_count);

    $start_page_first_result = $offsets['start_page_first_result'];
    $results_per_page = $offsets['results_per_page'];
    $number_of_pages = $offsets['number_of_pages'];

    if(is_null($search)){
      $users = $this->users->fetchPeopleSearch($start_page_first_result, $results_per_page, $filterOption, $filterOrder);
    }
    else{
      $users = $this->users->fetchPeopleSearch($start_page_first_result, $results_per_page, $filterOption, $filterOrder, $search);
    }

    $params = [
      'users' => $users,
      'page' => $page,
      'filterOption' => $filterOption,
      'filterOrder' => $filterOrder,
      'number_of_pages' => $number_of_pages,
      'start_page_first_result' => $start_page_first_result,
      'results_per_page' => $results_per_page,
      'search' => $search,
      'results_per_page_count' => $results_per_page_count
    ];

    return $this->display('content/people', 'people', $params);
  }

  public function courses(){
    $parameters = $this->getFilters();
    $page = $parameters['page'];
    $filterOption = $parameters['filterOption'];
    $filterOrder = $parameters['filterOrder'];
    $search = $parameters['search'];
    $results_per_page_count = $parameters['results_per_page_count'];

    if(is_null($search)){
      $course_count = $this->courses->getCoursesCount();
    }
    else{
      $course_count = $this->courses->getCourseCountSearch($search);
    }

    $offsets = $this->calculateOffsets($course_count, $page, $results_per_page_count);

    $start_page_first_result = $offsets['start_page_first_result'];
    $results_per_page = $offsets['results_per_page'];
    $number_of_pages = $offsets['number_of_pages'];

    if(is_null($search) && empty($search)){
       $courses = $this->courses->fetchCoursesSearch($start_page_first_result, $results_per_page, $filterOption, $filterOrder);
     }
    else{
      $courses = $this->courses->fetchCoursesSearch($start_page_first_result, $results_per_page, $filterOption, $filterOrder, $search);
    }

    $params = [
      'courses' => $courses,
      'page' => $page,
      'filterOption' => $filterOption,
      'filterOrder' => $filterOrder,
      'number_of_pages' => $number_of_pages,
      'start_page_first_result' => $start_page_first_result,
      'results_per_page' => $results_per_page,
      'search' => $search,
      'results_per_page_count' => $results_per_page_count
    ];

    return $this->display('content/courses', 'courses', $params);
  }

  public function forum(){
    $parameters = $this->getFilters();
    $page = $parameters['page'];
    $filterOption = $parameters['filterOption'];
    $filterOrder = $parameters['filterOrder'];
    $search = $parameters['search'];
    $results_per_page_count = $parameters['results_per_page_count'];

    if(is_null($search)){
      $forum_count = $this->forums->getForumCount();
    }
    else{
      $forum_count = $this->forums->getForumCountSearch($search);
    }

    $offsets = $this->calculateOffsets($forum_count, $page, $results_per_page_count);

    $start_page_first_result = $offsets['start_page_first_result'];
    $results_per_page = $offsets['results_per_page'];
    $number_of_pages = $offsets['number_of_pages'];

    if(is_null($search) && empty($search)){
       $forums = $this->forums->fetchForumsSearch($start_page_first_result, $results_per_page, $filterOption, $filterOrder);
     }
    else{
      $forums = $this->forums->fetchForumsSearch($start_page_first_result, $results_per_page, $filterOption, $filterOrder, $search);
    }

    $params = [
      'forums' => $forums,
      'page' => $page,
      'filterOption' => $filterOption,
      'filterOrder' => $filterOrder,
      'number_of_pages' => $number_of_pages,
      'start_page_first_result' => $start_page_first_result,
      'results_per_page' => $results_per_page,
      'search' => $search,
      'results_per_page_count' => $results_per_page_count
    ];

    return $this->display('content/forum', 'forum', $params);
  }

  public function toggleCourseToDegree(Request $request){
    $body = $request->getBody();
    $user = $this->users->getUser(Session::get(SESSION_USERID));

    $degreeID = $user["activeDegreeID"];

    if(is_null($degreeID)){
      $resp = ['success'=>false,'data'=>['Status'=>'No active degree']];
      return $this->jsonResponse($resp, 500);
    }

    $courseID = $body["courseID"];

    $isInActiveDegree = $this->courses->checkIfCourseExistsInActiveDegree($courseID);

    if($isInActiveDegree){
      $this->courses->deleteDegreeCourse($degreeID, $courseID);
      $resp = ['success'=>true,'data'=>['Status'=>'Deleted']];
      return $this->jsonResponse($resp, 200);
    }else{
      $this->courses->insertDegreeCourse($degreeID, $courseID);
      $resp = ['success'=>false,'data'=>['Status'=>'Inserted']];
      return $this->jsonResponse($resp, 200);
    }
  }
}
