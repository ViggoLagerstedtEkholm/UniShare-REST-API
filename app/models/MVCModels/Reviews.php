<?php

namespace App\Models\MVCModels;

use App\Includes\Validate;
use App\Core\Session;
use JetBrains\PhpStorm\Pure;

/**
 * Model for handling reviews from users.
 * @author Viggo Lagestedt Ekholm
 */
class Reviews extends Database implements IValidate
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

        if (strlen($params["text"]) < 200) {
            $errors[] = "DESCRIPTION_CHARS_NOT_ENOUGH";
        }

        return $errors;
    }

    /**
     * Get review count.
     * @param int $courseID
     * @return ?int
     */
    function getReviewCount(int $courseID): ?int
    {
        $sql = "SELECT Count(*) FROM review WHERE courseID = ?;";
        $result = $this->executeQuery($sql, 'i', array($courseID));
        return $result->fetch_assoc()["Count(*)"];
    }

    /**
     * Get review.
     * @param int $userID
     * @param int $courseID
     * @return ?array
     */
    function getReview(int $userID, int $courseID): ?array
    {
        $sql = "SELECT * FROM review WHERE userID = ? AND courseID = ?;";
        $result = $this->executeQuery($sql, 'ii', array($userID, $courseID));
        return $result->fetch_assoc();
    }

    /**
     * Get reviews between 2 integer intervals.
     * @param int $from
     * @param int $to
     * @param int $courseID
     * @return ?array
     */
    function getReviews(int $from, int $to, int $courseID): ?array
    {
        $sql = "SELECT review.*, userDisplayName, userImage
            FROM review
            JOIN users
            ON review.userID = users.usersID
            WHERE courseID = ?
            LIMIT ?,?";
        $result = $this->executeQuery($sql, 'iii', array($courseID, $from, $to));
        return $this->fetchResults($result);
    }

    /**
     * Insert review.
     * @param array $params
     * @return bool
     */
    function insertReview(array $params): bool
    {
        $sql = "INSERT INTO review (userID, courseID, text, fulfilling, environment, difficulty, grading, litterature, overall)
    values(?,?,?,?,?,?,?,?,?)
    ON DUPLICATE KEY UPDATE text = ?, fulfilling = ?, environment = ?, difficulty = ?, grading = ?, litterature = ?, overall = ?;";

        $userID = Session::get(SESSION_USERID);

        return $this->insertOrUpdate($sql, 'iisiiiiiisiiiiii', array(
            $userID, $params["courseID"], $params["text"],
            $params["fulfilling"], $params["environment"], $params["difficulty"],
            $params["grading"], $params["litterature"], $params["overall"],
            $params["text"], $params["fulfilling"], $params["environment"],
            $params["difficulty"], $params["grading"], $params["litterature"], $params["overall"]));
    }

    /**
     * Delete review.
     * @param int $userID
     * @param int $courseID
     */
    function deleteReview(int $userID, int $courseID)
    {
        $sql = "DELETE FROM review WHERE userID = ? AND courseID = ?;";
        $this->executeQuery($sql, 'ii', array($userID, $courseID));
    }
}
