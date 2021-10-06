<?php

namespace App\models;

use App\Core\Session;
use App\validation\ForumValidator;
use App\validation\PostValidator;
use JetBrains\PhpStorm\Pure;
use Throwable;

/**
 * Model for handling forum queries.
 * @author Viggo Lagestedt Ekholm
 */
class Forums extends Database implements IValidate
{
    /**
     * Check if the user input is sufficient enough.
     * @param array $params
     * @return array
     */
    public function validate(array $params): array
    {
        $errors = array();
        if(!ForumValidator::validTitle($params['title'])){
            $errors[] = INVALID_TITLE;
        }
        if(!ForumValidator::validTopic($params['topic'])){
            $errors[] = INVALID_TOPIC;
        }
        if(!PostValidator::validPost($params['text'])){
            $errors[] = INVALID_TEXT;
        }

        return $errors;
    }

    function getForumsCount(): int
    {
        $sql = "SELECT Count(*)
                FROM forum;";

        $result = $this->executeQuery($sql);
        return $result->fetch_assoc()['Count(*)'];
    }

    /**
     * Get specific forum.
     * @param int $forumID
     * @return false|array|null
     */
    function getForum(int $forumID): false|array|null
    {
        $sql = "SELECT * FROM forum WHERE forumID = ?;";
        $result = $this->executeQuery($sql, 'i', array($forumID));
        return $result->fetch_assoc();
    }

    /**
     * Insert forum transaction.
     * @param array $params
     * @return int|string|null
     * @throws Throwable
     */
    function insertForum(array $params): int|string|null
    {
        $userID = Session::get(SESSION_USERID);
        try {
            $this->getConnection()->begin_transaction();
            date_default_timezone_set("Europe/Stockholm");
            $date = date('Y-m-d H:i:s');

            $sql = "INSERT INTO forum (title, topic, creator, created) values(?,?,?,?);";
            $inserted = $this->insertOrUpdate($sql, 'ssis', array($params['title'], $params['topic'], $userID, $date));
            $forumID = $this->getConnection()->insert_id;

            if (!$inserted) {
                $this->getConnection()->rollback();
            }


            $sql = "INSERT INTO posts(userID, forumID, text, date) values(?,?,?,?);";
            $inserted = $this->insertOrUpdate($sql, 'iiss', array($userID, $forumID, $params["text"], $date));

            if (!$inserted) {
                $this->getConnection()->rollback();
            }

            $this->getConnection()->commit();
        } catch (Throwable $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        if ($inserted) {
            return $forumID;
        } else {
            return null;
        }
    }

    /**
     * Add view to forum.
     * @return array|null
     */
    function getTOP10Forums(): array|null
    {
        $sql = "SELECT *
            FROM forum
            ORDER BY views
            DESC
            LIMIT 10;";
        $result = $this->executeQuery($sql);
        return $this->fetchResults($result);
    }

    public function getPostsCount(int $forumID)
    {
        $sql = "SELECT Count(*)
                FROM posts
                WHERE forumID = ?";
        $this->executeQuery($sql,'i', array($forumID));
    }
}
