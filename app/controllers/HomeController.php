<?php

namespace App\controllers;

use App\core\Request;
use App\middleware\AuthenticationMiddleware;
use App\Models\Users;
use App\Models\Courses;
use App\Models\Forums;
use App\Core\Session;

/**
 * Home controller for handling the home page.
 * @author Viggo Lagestedt Ekholm
 */
class HomeController extends Controller
{
    private Users $users;
    private Courses $courses;
    private Forums $forums;

    public function __construct()
    {
        //$this->setMiddlewares(new AuthenticationMiddleware(['getCurrentUser']));

        $this->users = new Users();
        $this->courses = new Courses();
        $this->forums = new Forums();
    }

    /**
     * Get the current logged in user.
     * @return bool|string
     */
    public function getCurrentUser(): bool|string
    {
        $ID = Session::get(SESSION_USERID);
        $currentUser = $this->users->getUser($ID);

        $firstname = $currentUser['userFirstName'];
        $lastname = $currentUser['userLastName'];
        $email = $currentUser['userEmail'];
        $username = $currentUser['userDisplayName'];
        $lastOnline = $currentUser['lastOnline'];
        $visits = $currentUser['visits'];
        $userID = $currentUser['usersID'];

        $userImage = base64_encode($currentUser['userImage']);

        $resp = ['success'=>true, 'data'=> [
            'userID' => $userID,
            'firstName' => $firstname,
            'lastName' => $lastname,
            'email' => $email,
            'username' => $username,
            'lastOnline' => $lastOnline,
            'visits' => $visits,
            'image' => $userImage]];

        return $this->jsonResponse($resp, 200);
    }

    /**
     * Get the top 10 rated courses
     * @return bool|string
     */
    public function getTOP10Courses(): bool|string
    {
        $topRankedCourses = $this->courses->getTOP10Courses();
        $resp = ['TOP_RANKED_COURSES' => $topRankedCourses];
        return $this->jsonResponse($resp, 200);
    }

    /**
     * Get the top 10 viewed forums
     * @return bool|string
     */
    public function getTOP10Forums(): bool|string
    {
        $topViewedForums = $this->forums->getTOP10Forums();
        $resp = ['TOP_VIEWED_FORUMS' => $topViewedForums];
        return $this->jsonResponse($resp, 200);
    }
}
