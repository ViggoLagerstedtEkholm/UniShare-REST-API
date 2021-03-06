<?php

namespace App\models;

use App\Core\Session;
use App\validation\ReviewValidator;

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
    public function validate(array $params): array
    {
        $errors = array();

        $ratings = [
            "fulfilling" => $params["fulfilling"],
            "environment" => $params["environment"],
            "difficulty" => $params["difficulty"],
            "grading" => $params["grading"],
            "literature" => $params["literature"],
            "overall" => $params["overall"],
        ];

        if (!ReviewValidator::validRatings($ratings)){
            $errors[] = INVALID_RATING;
        }

        if (!ReviewValidator::validText($params["text"])){
            $errors[] = INVALID_REVIEW_TEXT;
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
     * Insert review.
     * @param array $params
     * @return bool
     */
    function insertReview(array $params): bool
    {
        $sql = "INSERT INTO review (userID, courseID, text, fulfilling, environment, difficulty, grading, litterature, overall, updated, added)
                values(?,?,?,?,?,?,?,?,?,?,?)
                ON DUPLICATE KEY UPDATE text = ?, fulfilling = ?, environment = ?, difficulty = ?, grading = ?, litterature = ?, overall = ?, updated = ?;";

        $userID = Session::get(SESSION_USERID);

        date_default_timezone_set("Europe/Stockholm");
        $date = date('Y-m-d H:i:s');

        return $this->insertOrUpdate($sql, 'iisiiiiiisssiiiiiis', array(
            $userID, $params["courseID"], $params["text"],
            $params["fulfilling"], $params["environment"], $params["difficulty"],
            $params["grading"], $params["literature"], $params["overall"], $date, $date,

            $params["text"], $params["fulfilling"], $params["environment"],
            $params["difficulty"], $params["grading"], $params["literature"], $params["overall"], $date));
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
