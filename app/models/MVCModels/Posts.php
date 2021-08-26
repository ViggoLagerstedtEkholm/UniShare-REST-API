<?php

namespace App\models\MVCModels;

use App\Includes\Validate;
use JetBrains\PhpStorm\Pure;

/**
 * Model for handling post queries.
 * @author Viggo Lagestedt Ekholm
 */
class Posts extends Database implements IValidate
{
    /**
     * Check if the user input is sufficient enough.
     * @param array $params
     * @return array
     */
    #[Pure] public function validate(array $params): array
    {
        $errors = array();

        if (Validate::arrayHasEmptyValue($params) === true) {
            $errors[] = EMPTY_FIELDS;
        }

        return $errors;
    }

    /**
     * Get post count.
     * @param int $forumID
     * @return int|null
     */
    function getPostCount(int $forumID): int|null
    {
        $sql = "SELECT COUNT(*) FROM posts WHERE forumID = ?;";
        $result = $this->executeQuery($sql, 'i', array($forumID));
        return $result->fetch_assoc()["COUNT(*)"];
    }

    /**
     * Add a post.
     * @param int $userID
     * @param int $forumID
     * @param string $text
     * @return bool
     */
    function addPost(int $userID, int $forumID, string $text): bool
    {
        $sql = "INSERT INTO posts(userID, forumID, text, date) values(?,?,?,?);";
        date_default_timezone_set("Europe/Stockholm");
        $date = date('Y-m-d H:i:s');
        return $this->insertOrUpdate($sql, 'iiss', array($userID, $forumID, $text, $date));
    }

    /**
     * Get posts between 2 integer intervals.
     * @param int $from
     * @param int $to
     * @param int $forumID
     * @return array|null
     */
    function getForumPostInterval(int $from, int $to, int $forumID): array|null
    {
        $sql = "SELECT posts.*, users.userDisplayName, users.userImage 
            FROM posts 
            JOIN users 
            ON posts.userID = users.usersID
            WHERE forumID = ?
            LIMIT ?, ?;";
        $result = $this->executeQuery($sql, 'iii', array($forumID, $from, $to));
        return $this->fetchResults($result);
    }
}