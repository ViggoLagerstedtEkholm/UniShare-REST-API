<?php
namespace App\Controllers;
use App\Core\Application;
use App\Models\MVCModels\Users;
use App\Models\MVCModels\Profiles;
use App\Models\MVCModels\Projects;
use App\Core\ImageHandler;

class HomeController extends Controller
{
  public function __construct()
  {
    $this->imageHandler = new ImageHandler();
    $this->users = new Users();
    $this->projects = new Projects();
  }

  public function view()
  {

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

    $params = [
      'users' => $users,
      'page' => $page,
      'filterOption' => $filterOption,
      'filterOrder' => $filterOrder,
      'number_of_pages' => $number_of_pages,
      'start_page_first_result' => $start_page_first_result,
      'results_per_page' => $results_per_page
    ];

    return $this->display('startpage', 'startpage', $params);
  }
}
?>
