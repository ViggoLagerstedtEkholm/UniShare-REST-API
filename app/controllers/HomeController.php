<?php

namespace App\controllers;

use App\Middleware\AuthenticationMiddleware;
use App\core\Handler;
use App\Core\Session;
use App\Models\Courses;
use App\Models\Forums;
use App\Models\Users;

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
        $this->setMiddlewares(new AuthenticationMiddleware(['getCurrentUser']));

        $this->users = new Users();
        $this->courses = new Courses();
        $this->forums = new Forums();
    }

    /**
     * Get the current logged in user.
     * @param Handler $handler
     * @return bool|string
     */
    public function getUser(Handler $handler): bool|string
    {
        $body = $handler->getRequest()->getBody();

        if(!empty($body['userID'])){
            $userID = $body['userID'];

            $user = $this->users->getUser($userID);
            $firstname = $user['userFirstName'];
            $lastname = $user['userLastName'];
            $email = $user['userEmail'];
            $username = $user['userDisplayName'];
            $lastOnline = $user['lastOnline'];
            $visits = $user['visits'];
            $userID = $user['usersID'];

            $userImage = base64_encode($user['userImage']);

            $resp = ['success'=>true, 'data'=> [
                'userID' => $userID,
                'firstName' => $firstname,
                'lastName' => $lastname,
                'email' => $email,
                'username' => $username,
                'lastOnline' => $lastOnline,
                'visits' => $visits,
                'image' => $userImage]];

            return $handler->getResponse()->jsonResponse($resp, 200);
        }else{
            return $handler->getResponse()->setStatusCode(404);
        }
    }

    /**
     * Get the top 10 rated courses
     * @param Handler $handler
     * @return bool|string
     */
    public function getTOP10Courses(Handler $handler): bool|string
    {
        $topRankedCourses = $this->courses->getTOP10Courses();
        $resp = ['TOP_RANKED_COURSES' => $topRankedCourses];
        return $handler->getResponse()->jsonResponse($resp, 200);
    }

    /**
     * Get the top 10 viewed forums
     * @param Handler $handler
     * @return bool|string
     */
    public function getTOP10Forums(Handler $handler): bool|string
    {
        $topViewedForums = $this->forums->getTOP10Forums();
        $resp = ['TOP_VIEWED_FORUMS' => $topViewedForums];
        return $handler->getResponse()->jsonResponse($resp, 200);
    }

    public function getWebsiteStatistics(Handler $handler): bool|string|null
    {
        $forumCount = $this->forums->getForumsCount();
        $usersCount = $this->users->getUsersCount();
        $courseCount = $this->courses->getCourseCount();

        $resp = [
            'forumCount' => $forumCount,
            'userCount' => $usersCount,
            'courseCount' => $courseCount,
        ];

        return $handler->getResponse()->jsonResponse($resp, 200);

    }
}
