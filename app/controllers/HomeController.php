<?php
namespace App\Controllers;
use App\Core\Application;
use App\Core\Session;
use App\Models\MVCModels\Users;
use App\Models\MVCModels\Profiles;
use App\Models\MVCModels\Projects;
use App\Models\MVCModels\Courses;
use App\Models\MVCModels\Degrees;
use App\Core\ImageHandler;

class HomeController extends Controller
{
  private $displayTopCount = 5;

  public function __construct()
  {
    $this->users = new Users();
    $this->courses = new Courses();
    $this->degrees = new Degrees();
  }

  public function view()
  {
    //$topRankedCourses = $this->courses->getTopCourses($this->displayTopCount);
    $currentUser = NULL;
    if(Session::exists(SESSION_USERID)){
      $ID = Session::get(SESSION_USERID);
      $currentUser = $this->users->getUser($ID);
    }

    $params = [
      "currentUser" => $currentUser
    ];

    return $this->display('startpage', 'startpage', $params);
  }
}
?>
