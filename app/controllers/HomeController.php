<?php
namespace App\Controllers;
use App\Models\MVCModels\Users;
use App\Models\MVCModels\Courses;
use App\Core\Session;

class HomeController extends Controller{
  private $users;
  private $courses;

  public function __construct()
  {
    $this->users = new Users();
    $this->courses = new Courses();
  }

  public function view()
  {
    $topRankedCourses = $this->courses->getTOP10Courses();
    $currentUser = NULL;
    if(Session::exists(SESSION_USERID)){
      $ID = Session::get(SESSION_USERID);
      $currentUser = $this->users->getUser($ID);
    }

    $params = [
      "currentUser" => $currentUser,
      "courses" => $topRankedCourses
    ];

    return $this->display('startpage', 'startpage', $params);
  }
}
?>
