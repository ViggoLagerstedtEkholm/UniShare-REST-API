<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use App\Controllers\HomeController;
use App\Controllers\AuthenticationController;
use App\Controllers\ProfileController;

session_start();

$homeController = new HomeController();
$authenticationController = new AuthenticationController();
$profileController = new ProfileController();

$app = new Application(dirname(__DIR__));


$app->router->get('/', [$homeController, 'view']);

$app->router->get('/register', [$authenticationController, 'view_register']);
$app->router->post('/register', [$authenticationController, 'register']);

$app->router->get('/login', [$authenticationController, 'view_login']);
$app->router->post('/login', [$authenticationController, 'login']);

$app->router->post('/login/test1', [$authenticationController, 'test1']);
$app->router->post('/login/test2', [$authenticationController, 'test2']);

$app->router->get('/logout', [$authenticationController, 'logout']);

$app->router->get('/profile', [$profileController, 'view']);
$app->router->post('/profile/upload/image', [$profileController, 'uploadImage']);
$app->router->post('/profile/upload/project', [$profileController, 'uploadProject']);


$app->run();
