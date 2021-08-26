<?php
namespace App\controllers;
use App\Models\MVCModels\Users;
use App\Models\MVCModels\Courses;
use App\Models\MVCModels\Forums;
use App\Core\Request;
use App\Core\Session;

/**
 * Content controller for handling searching and filtering website content.
 * @author Viggo Lagestedt Ekholm
 */
class ContentController extends Controller
{
  private Users $users;
  private Courses $courses;
  private Forums $forums;

  public function __construct()
  {
    $this->users = new Users();
    $this->courses = new Courses();
    $this->forums = new Forums();
  }

  /**
   * Get the GET parameters used for filtering and pagination.
   * @return array
   */
  private function getFilters(): array
  {
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

  /**
   * Use the parameters to calculate the amount of pages required to showcase
   * all the items. The method filters people.
   * @return string
   */
  public function people(): string
  {
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

  /**
   * Use the parameters to calculate the amount of pages required to showcase
   * all the items. The method filters courses.
   * @return string
   */
  public function courses(): string
  {
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

  /**
   * Use the parameters to calculate the amount of pages required to showcase
   * all the items. The method filters forums.
   * @return string
   */
  public function forum(): string
  {
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

    /**
     * This method handles the adding and removing of courses from our active degree.
     * We make sure to check if the user has an active degree and informs the user
     * if they need to add one. We return HTTP status codes to handle a valid response.
     * @param Request $request
     * @return false|string
     */
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
    }else{
      $this->courses->insertDegreeCourse($degreeID, $courseID);
      $resp = ['success'=>false,'data'=>['Status'=>'Inserted']];
    }

    return $this->jsonResponse($resp, 200);
  }
}
