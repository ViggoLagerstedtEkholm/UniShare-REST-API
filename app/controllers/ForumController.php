<?php

namespace App\controllers;

use App\core\Exceptions\NotFoundException;
use App\Core\Request;
use App\Middleware\AuthenticationMiddleware;
use App\Models\Forums;
use App\Models\Posts;
use App\Core\Application;
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

    public function getForum(Request $request): bool|string|null
    {
        $body = $request->getBody();
        $forumID = $body['forumID'];
        $forum = $this->forums->getForum($forumID);
        return $this->jsonResponse($forum, 200);
    }


    /**
     * This method handles adding a forum.
     * @param Request $request
     * @return bool|string|null
     * @throws Throwable
     */
    public function addForum(Request $request): bool|string|null
    {
        $body = $request->getBody();

        $errors = $this->forums->validate($body);

        if (count($errors) > 0) {
            $errorList = http_build_query(array('error' => $errors));
            return $this->jsonResponse($errorList, 500);
        }

        $forumID = $this->forums->insertForum($body);

        if (is_null($forumID)) {
            return $this->jsonResponse(false, 500);
        }
        return $this->jsonResponse(true, 200);
    }
}
