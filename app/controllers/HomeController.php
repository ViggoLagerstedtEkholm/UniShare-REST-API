<?php
namespace App\Controllers;
use App\Models\MVCModels\Users;
use App\Models\MVCModels\Courses;
use App\Models\MVCModels\Forums;
use App\Core\Session;

class HomeController extends Controller{
  private $users;
  private $courses;
  private $forums;
  
  public function __construct()
  {
    $this->users = new Users();
    $this->courses = new Courses();
    $this->forums = new Forums();
  }

  public function view()
  {
    $topRankedCourses = $this->courses->getTOP10Courses();
    $topViewedForums = $this->forums->getTOP10Forums();
    
    $currentUser = NULL;
    if(Session::exists(SESSION_USERID)){
      $ID = Session::get(SESSION_USERID);
      $currentUser = $this->users->getUser($ID);
    }

    $params = [
      "currentUser" => $currentUser,
      "courses" => $topRankedCourses,
      "forums" => $topViewedForums
    ];

    return $this->display('startpage', 'startpage', $params);
  }
}
?>
