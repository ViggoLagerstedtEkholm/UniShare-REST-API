<?php

namespace App\models;

use App\core\Session;
use JetBrains\PhpStorm\Pure;

/**
 * Model for querying comments.
 * @author Viggo Lagestedt Ekholm
 */
class Comments extends Database implements IValidate
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
     * Add a comment to the database.
     * @param int $posterID
     * @param int $profileID
     * @param string $comment
     * @return bool
     */
    function addComment(int $posterID, int $profileID, string $comment): bool
    {
        date_default_timezone_set("Europe/Stockholm");
        $date = date('Y-m-d H:i:s');

        $sql = "INSERT INTO profilecomment (text, date, author, profile) values(?,?,?,?);";

        return $this->insertOrUpdate($sql, 'ssii', array($comment, $date, $posterID, $profileID));
    }

    /**
     * Check if a given user ID is the author of a certain comment.
     * @param int $userID
     * @param int $commentIDtoDelete
     * @return bool
     */
    function checkIfUserAuthor(int $userID, int $commentIDtoDelete): bool
    {
        $sql = "SELECT commentID FROM profilecomment WHERE author = ? OR profile = ?;";
        $result = $this->executeQuery($sql, 'ii', array($userID, Session::get(SESSION_USERID)));

        while ($row = $result->fetch_array()) {
            if ($row["commentID"] == $commentIDtoDelete) {
                return true;
            }
        }
        return false;
    }

    /**
     * Delete comment after ID.
     * @param int $commentID
     */
    function deleteComment(int $commentID)
    {
        $sql = "DELETE FROM profilecomment WHERE commentID = ?;";
        $this->executeQuery($sql, 'i', array($commentID));
    }
}
