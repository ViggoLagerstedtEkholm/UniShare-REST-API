<?php

namespace App\controllers;

use App\core\Handler;
use App\Middleware\AuthenticationMiddleware;
use App\Models\Forums;
use Throwable;

/**
 * Forum controller for handling forums.
 * @author Viggo Lagestedt Ekholm
 */
class ForumController extends Controller
{
    private Forums $forums;

    function __construct()
    {
        $this->setMiddlewares(new AuthenticationMiddleware(['addForumView', 'addForum']));

        $this->forums = new Forums();
    }

    public function getForum(Handler $handler): bool|string|null
    {
        $body = $handler->getRequest()->getBody();
        $forumID = $body['forumID'];
        $forum = $this->forums->getForum($forumID);
        return $handler->getResponse()->jsonResponse($forum, 200);
    }

    /**
     * This method handles adding a forum.
     * @param Handler $handler
     * @return bool|string|null
     * @throws Throwable
     */
    public function addForum(Handler $handler): bool|string|null
    {
        $body = $handler->getRequest()->getBody();

        $errors = $this->forums->validate($body);

        if (count($errors) > 0) {
            return $handler->getResponse()->jsonResponse($errors, 500);
        }

        $forumID = $this->forums->insertForum($body);

        if (is_null($forumID)) {
            return $handler->getResponse()->jsonResponse(false, 500);
        }
        return $handler->getResponse()->jsonResponse(true, 200);
    }
}
