<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use App\Core\Cookie;
use App\Core\Session;

use App\Controllers\HomeController;
use App\Controllers\AuthenticationController;
use App\Controllers\ProfileController;
use App\Controllers\ProjectController;
use App\Controllers\SettingsController;
use App\Controllers\ContentController;
use App\Controllers\CourseController;
use App\Controllers\AdminController;
use App\Controllers\DegreeController;
use App\Controllers\RequestController;
use App\Controllers\ForumController;
use App\Controllers\PublicationController;

require_once(__DIR__ . '/config.php');

session_start();

$homeController = new HomeController();
$authenticationController = new AuthenticationController();
$profileController = new ProfileController();
$projectController = new ProjectController();
$settingsController = new SettingsController();
$contentController = new ContentController();
$courseController = new CourseController();
$adminController = new AdminController();
$degreeController = new DegreeController();
$requestController = new RequestController();
$forumController = new ForumController();
$publicationController = new PublicationController();

if(Cookie::exists(REMEMBER_ME_COOKIE_NAME) && !Session::exists(SESSION_USERID)){
  $authenticationController->loginWithCookie();
}

$app = new Application(dirname(__DIR__));

$app->router->get('/', [$homeController, 'view']);

$app->router->get('/register', [$authenticationController, 'view_register']);
$app->router->post('/register', [$authenticationController, 'register']);
$app->router->get('/login', [$authenticationController, 'view_login']);
$app->router->post('/login', [$authenticationController, 'login']);
$app->router->get('/logout', [$authenticationController, 'logout']);

$app->router->get('/profile', [$profileController, 'view']);
$app->router->post('/profile/upload/image', [$profileController, 'uploadImage']);
$app->router->post('/profile/upload/image', [$profileController, 'uploadImage']);
$app->router->post('/profile/add/comment', [$profileController, 'addComment']);
$app->router->post('/profile/delete/comment', [$profileController, 'deleteComment']);
$app->router->post('/profile/delete/course', [$profileController, 'removeCourseFromDegree']);

$app->router->get('/project_add', [$projectController, 'add']);
$app->router->get('/project_update', [$projectController, 'update']);
$app->router->post('/project/upload', [$projectController, 'uploadProject']);
$app->router->post('/project/delete', [$projectController, 'deleteProject']);
$app->router->post('/project/update', [$projectController, 'updateProject']);
$app->router->get('/project/get', [$projectController, 'getProjectForEdit']);

$app->router->get('/settings', [$settingsController, 'view']);
$app->router->get('/settings/getsettings', [$settingsController, 'fetch']);
$app->router->post('/settings/deleteAccount', [$settingsController, 'deleteAccount']);
$app->router->post('/settings/update', [$settingsController, 'update']);

$app->router->get('/courses', [$courseController, 'view']);
$app->router->get('/review', [$courseController, 'review']);
$app->router->get('/course/getrate', [$courseController, 'getRate']);
$app->router->post('/course/setrate', [$courseController, 'setRate']);
$app->router->post('/course/upload/review', [$courseController, 'uploadReview']);
$app->router->post('/course/request', [$courseController, 'request']);

$app->router->get('/degree_new', [$degreeController, 'add']);
$app->router->get('/degree_update', [$degreeController, 'update']);
$app->router->get('/degrees/get', [$degreeController, 'getDegrees']);
$app->router->post('/degree/upload', [$degreeController, 'uploadDegree']);
$app->router->post('/degree/remove', [$degreeController, 'removeDegree']);

$app->router->get('/searchPeople', [$contentController, 'people']);
$app->router->get('/searchDegrees', [$contentController, 'degrees']);
$app->router->get('/searchCourses', [$contentController, 'courses']);
$app->router->post('/toggleCourseToDegree', [$contentController, 'toggleCourseToDegree']);

$app->router->get('/admin', [$adminController, 'view']);
$app->router->post('/admin/course/add', [$adminController, 'addCourse']);
$app->router->post('/admin/course/remove', [$adminController, 'removeCourse']);
$app->router->post('/admin/course/update', [$adminController, 'updateCourse']);
$app->router->post('/admin/users/add', [$adminController, 'addUser']);
$app->router->post('/admin/users/remove', [$adminController, 'removeUser']);
$app->router->post('/admin/users/update', [$adminController, 'updateUser']);

$app->router->get('/request', [$requestController, 'view']);
$app->router->post('/request/upload', [$requestController, 'uploadRequest']);
$app->router->post('/request/delete', [$requestController, 'deletePending']);

$app->router->get('/forum', [$forumController, 'view']);

$app->router->get('/publications', [$publicationController, 'view']);

$app->run();
