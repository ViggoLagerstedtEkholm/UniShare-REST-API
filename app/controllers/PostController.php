<?php

namespace App\controllers;

use App\core\Exceptions\NotFoundException;
use App\Core\Request;
use App\Middleware\AuthenticationMiddleware;
use App\Models\Forums;
use App\Models\Posts;
use App\Core\Session;
use App\Core\Application;

/**
 * Post controller for handling posts.
 * @author Viggo Lagestedt Ekholm
 */
class PostController extends Controller
{
    private Posts $posts;
    private Forums $forums;

    function __construct()
    {
        $this->setMiddlewares(new AuthenticationMiddleware(['view', 'update', 'post', 'delete', 'addForum']));

        $this->posts = new Posts();
        $this->forums = new Forums();
    }

    /**
     * This method handles adding new posts.
     * @param Request $request
     * @return bool|string|null
     */
    public function addPost(Request $request): bool|string|null
    {
        $body = $request->getBody();

        $forumID = $body['forumID'];
        $text = $body['text'];
        $userID = Session::get(SESSION_USERID);

        $errors = $this->posts->validate($body);

        if (count($errors) > 0) {
            $errorList = http_build_query(array('error' => $errors));
            return $this->jsonResponse($errorList, 500);
        }

        $inserted = $this->posts->addPost($userID, $forumID, $text);

        if (!$inserted) {
            return $this->setStatusCode(500);
        }else{
            return $this->setStatusCode(200);
        }
    }
}
