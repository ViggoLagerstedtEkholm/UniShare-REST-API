<?php

namespace App\models\MVCModels;

use App\Includes\Validate;
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
        $errors = array();

        if (Validate::arrayHasEmptyValue($params) === true) {
            $errors[] = EMPTY_FIELDS;
        }

        return $errors;
    }

    /**
     * Get the amount of total comments in the comments table.
     * @param int $userID
     * @return int
     */
    function getCommentCount(int $userID): int
    {
        $sql = "SELECT Count(*) FROM profilecomment WHERE profile = ?;";
        $result = $this->executeQuery($sql, 'i', array($userID));
        return $result->fetch_assoc()["Count(*)"];
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
        $date = date("Y-m-d", time());

        $sql = "INSERT INTO profileComment (text, date, author, profile) values(?,?,?,?);";

        return $this->insertOrUpdate($sql, 'ssii', array($comment, $date, $posterID, $profileID));
    }

    /**
     * Check if a given user ID is the author of a certain comment.
     * @param int $userID
     * @param int $commentIDtoDelete
     * @return bool
     */
    function checkIfUserAuthor(int $userID, int $commentIDtoDelete)
    {
        $sql = "SELECT commentID FROM profilecomment WHERE author = ?;";
        $result = $this->executeQuery($sql, 'i', array($userID));

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

    /**
     * Get the comments from a given interval.
     * @param int $from
     * @param int $to
     * @param int $profileID
     * @return array
     */
    function getComments(int $from, int $to, int $profileID): array
    {
        $sql = "SELECT profileComment.*, userImage, userDisplayName
            FROM profileComment
            JOIN users
            ON author = usersID
            WHERE profile = ?
            LIMIT ?, ?;";
        $result = $this->executeQuery($sql, 'iii', array($profileID, $from, $to));
        return $this->fetchResults($result);
    }
}
