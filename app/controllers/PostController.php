<?php

namespace App\controllers;

use App\core\Handler;
use App\Core\Session;
use App\Middleware\AuthenticationMiddleware;
use App\Models\Posts;

/**
 * Post controller for handling posts.
 * @author Viggo Lagestedt Ekholm
 */
class PostController extends Controller
{
    private Posts $posts;

    function __construct()
    {
        $this->setMiddlewares(new AuthenticationMiddleware(['addPost']));

        $this->posts = new Posts();
    }

    /**
     * This method handles adding new posts.
     * @param Handler $handler
     * @return bool|string|null
     */
    public function addPost(Handler $handler): bool|string|null
    {
        $body = $handler->getRequest()->getBody();

        $forumID = $body['forumID'];
        $text = $body['text'];
        $userID = Session::get(SESSION_USERID);

        $errors = $this->posts->validate($body);

        if (count($errors) > 0) {
            $errorList = http_build_query(array('error' => $errors));
            return $handler->getResponse()->jsonResponse($errorList, 500);
        }

        $inserted = $this->posts->addPost($userID, $forumID, $text);

        if (!$inserted) {
            $handler->getResponse()->setStatusCode(500);
        }else{
            $handler->getResponse()->setStatusCode(200);
        }
    }
}
