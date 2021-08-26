<?php

namespace App\controllers;

use App\Core\Request;
use App\Middleware\AuthenticationMiddleware;
use App\Models\MVCModels\Forums;
use App\Models\MVCModels\Posts;
use App\Core\Application;
use Throwable;

/**
 * Forum controller for handling forums.
 * @author Viggo Lagestedt Ekholm
 */
class ForumController extends Controller
{
    private Forums $forums;
    private Posts $posts;

    function __construct()
    {
        $this->setMiddlewares(new AuthenticationMiddleware(['addForumView', 'addForum']));

        $this->forums = new Forums();
        $this->posts = new Posts();
    }

    /**
     * This method shows the forum page.
     * @param Request $request
     * @return string
     */
    public function view(Request $request): string
    {
        $body = $request->getBody();
        $forumID = $body["ID"];
        if (!is_null($forumID)) {

            $this->forums->addViews($forumID);

            if (isset($_GET['page'])) {
                $page = $_GET['page'];
            } else {
                $page = 1;
            }

            $post_count = $this->posts->getPostCount($forumID);

            $offsets = $this->calculateOffsets($post_count, $page, 10);
            $start_page_first_result = $offsets['start_page_first_result'];
            $results_per_page = $offsets['results_per_page'];
            $number_of_pages = $offsets['number_of_pages'];

            $posts = $this->posts->getForumPostInterval($start_page_first_result, $results_per_page, $forumID);
            $forum = $this->forums->getForum($forumID);

            $params = [
                'posts' => $posts,
                'forum' => $forum,
                'page' => $page,
                'start_page_first_result' => $start_page_first_result,
                'results_per_page' => $results_per_page,
                'number_of_pages' => $number_of_pages
            ];

            return $this->display('forum/display', 'forum', $params);
        }
    }

    /**
     * This method shows the add forum page.
     * @return string
     */
    public function addForumView(): string
    {
        return $this->display('forum/add', 'forum', []);
    }

    /**
     * This method handles adding a forum.
     * @param Request $request
     * @throws Throwable
     */
    public function addForum(Request $request)
    {
        $body = $request->getBody();

        $errors = $this->forums->validate($body);

        if (count($errors) > 0) {
            $errorList = http_build_query(array('error' => $errors));
            Application::$app->redirect("/UniShare/forum&$errorList");
            exit();
        }

        $forumID = $this->forums->insertForum($body);

        if (!is_null($forumID)) {
            Application::$app->redirect("/UniShare/forum?ID=$forumID");
        } else {
            Application::$app->redirect("/UniShare/forum?error=unexpectedly");
        }
    }

    //TODO
    public function updateForum(Request $request)
    {
        $body = $request->getBody();
    }

    //TODO
    public function deleteForum(Request $request)
    {
        $body = $request->getBody();
    }
}
