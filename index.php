<?php
require_once __DIR__ . '/vendor/autoload.php';

session_set_cookie_params(['SameSite' => 'None', 'Secure' => true]);
session_start();

// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
    // you want to allow, and if so:
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
    header("Access-Control-Allow-Methods: GET, POST");
    header("Access-Control-Allow-Headers: *");
}

use App\core\Application;
use App\core\Cookie;
use App\core\Session;

use App\controllers\HomeController;
use App\controllers\AuthenticationController;
use App\controllers\ProfileController;
use App\controllers\ProjectController;
use App\controllers\SettingsController;
use App\controllers\ContentController;
use App\controllers\CourseController;
use App\controllers\AdminController;
use App\controllers\DegreeController;
use App\controllers\RequestController;
use App\controllers\ForumController;
use App\controllers\ReviewController;
use App\controllers\PostController;
use App\controllers\FriendsController;

require_once(__DIR__ . '/config.php');


if(Cookie::exists(REMEMBER_ME_COOKIE_NAME) && !Session::exists(SESSION_USERID)){
  $authenticationController = new AuthenticationController();
  $authenticationController->loginWithCookie();
}

$app = new Application(dirname(__DIR__));

$app->router->post('/request', [FriendsController::class, 'request']);
$app->router->post('/reject', [FriendsController::class, 'reject']);
$app->router->post('/accept', [FriendsController::class, 'accept']);
$app->router->post('/delete', [FriendsController::class, 'delete']);
$app->router->get('/received/pending', [FriendsController::class, 'getMyPendingReceivedRequest']);
$app->router->get('/sent/pending', [FriendsController::class, 'getMyPendingSentRequest']);
$app->router->get('/get/friends', [FriendsController::class, 'getFriends']);

$app->router->get('/getCurrentUser', [HomeController::class, 'getCurrentUser']);
$app->router->get('/getTOP10Courses', [HomeController::class, 'getTOP10Courses']);
$app->router->get('/getTOP10Forums', [HomeController::class, 'getTOP10Forums']);

$app->router->get('/isLoggedIn', [AuthenticationController::class, 'isLoggedIn']);
$app->router->post('/register', [AuthenticationController::class, 'register']);
$app->router->post('/login', [AuthenticationController::class, 'login']);
$app->router->post('/logout', [AuthenticationController::class, 'logout']);

$app->router->get('/profile/sideHUD', [ProfileController::class, 'getSideHUDInfo']);
$app->router->post('/profile/upload/image', [ProfileController::class, 'uploadImage']);
$app->router->post('/profile/append/visits', [ProfileController::class, 'appendVisits']);
$app->router->post('/profile/add/comment', [ProfileController::class, 'addComment']);
$app->router->post('/profile/delete/comment', [ProfileController::class, 'deleteComment']);
$app->router->post('/profile/delete/course', [ProfileController::class, 'removeCourseFromDegree']);

$app->router->get('/project/get/all', [ProjectController::class, 'get']);
$app->router->get('/project/get', [ProjectController::class, 'edit']);
$app->router->post('/project/upload', [ProjectController::class, 'upload']);
$app->router->post('/project/delete', [ProjectController::class, 'delete']);
$app->router->post('/project/update', [ProjectController::class, 'update']);

$app->router->get('/settings/get', [SettingsController::class, 'fetch']);
$app->router->post('/settings/deleteAccount', [SettingsController::class, 'deleteAccount']);
$app->router->post('/settings/update', [SettingsController::class, 'update']);

$app->router->get('/course/get', [CourseController::class, 'getCourse']);
$app->router->get('/course/statistics', [CourseController::class, 'getCourseStatistics']);
$app->router->get('/course/get/rate', [CourseController::class, 'getRate']);
$app->router->post('/course/set/rate', [CourseController::class, 'setRate']);
$app->router->get('/course/getGraphData', [CourseController::class, 'getRatingGraphData']);
$app->router->post('/course/request', [CourseController::class, 'request']);

$app->router->get('/review/get', [ReviewController::class, 'getReview']);
$app->router->post('/review/upload', [ReviewController::class, 'uploadReview']);
$app->router->post('/review/delete', [ReviewController::class, 'deleteReview']);

$app->router->get('/degree/get', [DegreeController::class, 'getDegree']);
$app->router->get('/degree/get/all', [DegreeController::class, 'getDegrees']);
$app->router->get('/degree/get/settings', [DegreeController::class, 'getDegreesSettings']);
$app->router->get('/degree/get/active', [DegreeController::class, 'getActiveDegreeID']);
$app->router->post('/degree/upload', [DegreeController::class, 'uploadDegree']);
$app->router->post('/degree/remove', [DegreeController::class, 'deleteDegree']);
$app->router->post('/degree/update', [DegreeController::class, 'updateDegree']);
$app->router->post('/degree/toggle/course', [DegreeController::class, 'toggleCourseToDegree']);

$app->router->get('/search/comments', [ContentController::class, 'comments']);
$app->router->get('/search/reviews', [ContentController::class, 'reviews']);
$app->router->get('/search/people', [ContentController::class, 'people']);
$app->router->get('/search/courses', [ContentController::class, 'courses']);
$app->router->get('/search/forums', [ContentController::class, 'forum']);
$app->router->get('/search/posts', [ContentController::class, 'posts']);
$app->router->get('/search/requests', [ContentController::class, 'requests']);
$app->router->get('/search/profile/ratings', [ContentController::class, 'profileTotalRatings']);
$app->router->get('/search/profile/reviews', [ContentController::class, 'profileTotalReviews']);

$app->router->get('/admin/course/requests', [AdminController::class, 'getRequestedCourses']);
$app->router->post('/admin/users/suspend', [AdminController::class, 'suspendUser']);
$app->router->post('/admin/users/enable', [AdminController::class, 'enableUser']);
$app->router->post('/admin/users/remove', [AdminController::class, 'deleteUser']);
$app->router->post('/admin/course/approve', [AdminController::class, 'approveRequest']);
$app->router->post('/admin/course/deny', [AdminController::class, 'denyRequest']);

$app->router->get('/request/courses', [RequestController::class, 'getRequests']);
$app->router->post('/request/upload', [RequestController::class, 'uploadRequest']);
$app->router->post('/request/delete', [RequestController::class, 'deletePending']);

$app->router->get('/forum/get', [ForumController::class, 'getForum']);
$app->router->post('/forum/add', [ForumController::class, 'addForum']);

$app->router->post('/post/add', [PostController::class, 'addPost']);

$app->run();
