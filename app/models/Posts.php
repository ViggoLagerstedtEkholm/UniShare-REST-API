<?php

namespace App\models;

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
        return array();
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
}