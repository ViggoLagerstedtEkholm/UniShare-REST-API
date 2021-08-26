<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use App\Core\Cookie;
use App\Core\Session;

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
use App\controllers\PublicationController;
use App\controllers\ReviewController;
use App\controllers\PostController;

require_once(__DIR__ . '/config.php');

session_start();

if(Cookie::exists(REMEMBER_ME_COOKIE_NAME) && !Session::exists(SESSION_USERID)){
  $authenticationController = new AuthenticationController();
  $authenticationController->loginWithCookie();
}

$app = new Application(dirname(__DIR__));

$app->router->get('/', [HomeController::class, 'view']);

$app->router->get('/register', [AuthenticationController::class, 'view_register']);
$app->router->post('/register', [AuthenticationController::class, 'register']);
$app->router->get('/login', [AuthenticationController::class, 'view_login']);
$app->router->post('/login', [AuthenticationController::class, 'login']);
$app->router->get('/logout', [AuthenticationController::class, 'logout']);

$app->router->get('/profile', [ProfileController::class, 'view']);
$app->router->post('/profile/upload/image', [ProfileController::class, 'uploadImage']);
$app->router->post('/profile/upload/image', [ProfileController::class, 'uploadImage']);
$app->router->post('/profile/add/comment', [ProfileController::class, 'addComment']);
$app->router->post('/profile/delete/comment', [ProfileController::class, 'deleteComment']);
$app->router->post('/profile/delete/course', [ProfileController::class, 'removeCourseFromDegree']);

$app->router->get('/project/add', [ProjectController::class, 'add']);
$app->router->get('/project/update', [ProjectController::class, 'update']);
$app->router->get('/project/get', [ProjectController::class, 'getProjectForEdit']);
$app->router->post('/project/upload', [ProjectController::class, 'uploadProject']);
$app->router->post('/project/delete', [ProjectController::class, 'deleteProject']);
$app->router->post('/project/update', [ProjectController::class, 'updateProject']);

$app->router->get('/settings', [SettingsController::class, 'view']);
$app->router->get('/settings/getsettings', [SettingsController::class, 'fetch']);
$app->router->post('/settings/deleteAccount', [SettingsController::class, 'deleteAccount']);
$app->router->post('/settings/update', [SettingsController::class, 'update']);

$app->router->get('/courses', [CourseController::class, 'view']);
$app->router->get('/course/getrate', [CourseController::class, 'getRate']);
$app->router->get('/course/getGraphData', [CourseController::class, 'getRatingGraphData']);
$app->router->post('/course/setrate', [CourseController::class, 'setRate']);
$app->router->post('/course/request', [CourseController::class, 'request']);

$app->router->get('/review', [ReviewController::class, 'review']);
$app->router->get('/review/get', [ReviewController::class, 'getReview']);
$app->router->post('/review/upload', [ReviewController::class, 'uploadReview']);
$app->router->post('/review/delete', [ReviewController::class, 'deleteReview']);

$app->router->get('/degree/new', [DegreeController::class, 'add']);
$app->router->get('/degree/update', [DegreeController::class, 'update']);
$app->router->get('/degree/get', [DegreeController::class, 'getDegree']);
$app->router->get('/degree/get/names', [DegreeController::class, 'getDegrees']);
$app->router->post('/degree/upload', [DegreeController::class, 'uploadDegree']);
$app->router->post('/degree/remove', [DegreeController::class, 'deleteDegree']);
$app->router->post('/degree/update', [DegreeController::class, 'updateDegree']);

$app->router->get('/search/people', [ContentController::class, 'people']);
$app->router->get('/search/courses', [ContentController::class, 'courses']);
$app->router->get('/search/forums', [ContentController::class, 'forum']);
$app->router->get('/searchCourses', [ContentController::class, 'courses']);
$app->router->post('/toggleCourseToDegree', [ContentController::class, 'toggleCourseToDegree']);

$app->router->get('/admin', [AdminController::class, 'view']);
$app->router->post('/admin/course/add', [AdminController::class, 'addCourse']);
$app->router->post('/admin/course/remove', [AdminController::class, 'removeCourse']);
$app->router->post('/admin/course/update', [AdminController::class, 'updateCourse']);
$app->router->post('/admin/users/add', [AdminController::class, 'addUser']);
$app->router->post('/admin/users/remove', [AdminController::class, 'removeUser']);
$app->router->post('/admin/users/update', [AdminController::class, 'updateUser']);
$app->router->post('/admin/course/approve', [AdminController::class, 'approveRequest']);
$app->router->post('/admin/course/deny', [AdminController::class, 'denyRequest']);

$app->router->get('/request', [RequestController::class, 'view']);
$app->router->post('/request/upload', [RequestController::class, 'uploadRequest']);
$app->router->post('/request/delete', [RequestController::class, 'deletePending']);

$app->router->get('/forum', [ForumController::class, 'view']);
$app->router->get('/forum/add', [ForumController::class, 'addForumView']);
$app->router->post('/forum/add', [ForumController::class, 'addForum']);

$app->router->get('/post', [PostController::class, 'view']);
$app->router->post('/post/add', [PostController::class, 'addPost']);


$app->router->get('/publications', [PublicationController::class, 'view']);

$app->run();
