<?php

namespace App\models;

use App\Core\Session;
use App\validation\RequestValidation;
use App\validation\SharedValidation;
use mysqli_result;

/**
 * Model for handling courses.
 * @author Viggo Lagestedt Ekholm
 */
class Courses extends Database
{
    function getCourseCount(): int
    {
        $sql = "SELECT Count(*)
               FROM courses;";
        $result = $this->executeQuery($sql);
        return $result->fetch_assoc()['Count(*)'];
    }

    /**
     * Get the top 10 highest rated courses.
     * @return array courses.
     */
    function getTOP10Courses(): array
    {
        $sql = "SELECT ROUND(AVG(rating),2)  AS average_rating, courses.*
            FROM rating
            JOIN courses
            ON rating.courseID = courses.courseID
            GROUP BY courses.courseID
            ORDER BY average_rating DESC
            LIMIT 10;";

        $result = $this->executeQuery($sql);
        return $this->fetchResults($result);
    }

    /**
     * Get the popularity rank of a given course ID.
     * @param int $courseID
     * @return bool|mysqli_result
     */
    function getPopularityRank(int $courseID): bool|mysqli_result
    {
        $sql = "SELECT *
            FROM
            (SELECT courseID, COUNT(rating), ROW_NUMBER() OVER (ORDER BY COUNT(rating) DESC) AS POPULARITY_RANK
            FROM rating
            GROUP BY courseID
            ORDER BY COUNT(rating) DESC) AS POPULARITY
            WHERE POPULARITY.courseID = ?;";

        return $this->executeQuery($sql, "i", array($courseID));
    }

    /**
     * Get the rating rank of a given course ID.
     * @param int $courseID
     * @return bool|mysqli_result
     */
    function getOverallRankingRating(int $courseID): bool|mysqli_result
    {
        $sql = "SELECT *
            FROM
            (SELECT courseID, AVG(rating), ROW_NUMBER() OVER (ORDER BY AVG(rating) DESC) AS RATING_RANK
            FROM rating
            GROUP BY courseID
            ORDER BY AVG(rating) DESC) AS RANKINGS
            WHERE RANKINGS.courseID = ?;";

        return $this->executeQuery($sql, "i", array($courseID));
    }

    /**
     * Get the arithmetic mean score of a given course.
     * @param int $courseID
     * @return array
     */
    function getArithmeticMeanScore(int $courseID): array
    {
        $sql = "SELECT ROUND(AVG(rating),2) AS rating, COUNT(rating) AS amount FROM rating WHERE courseID = ?;";
        $result = $this->executeQuery($sql, 'i', array($courseID));
        return $result->fetch_assoc();
    }

    /**
     * Set the rate of a given course.
     * @param int $userID
     * @param int $courseID
     * @param int $rating
     * @return bool
     */
    function setRate(int $userID, int $courseID, int $rating): bool
    {
        $sql = "INSERT INTO rating (userID, courseID, rating) values(?,?,?) ON DUPLICATE KEY UPDATE rating = ?;";
        $this->insertOrUpdate($sql, 'iiii', array($userID, $courseID, $rating, $rating));
    }

    /**
     * Get the rate of a given course.
     * @param int $userID
     * @param int $courseID
     * @return int|string
     */
    function getRate(int $userID, int $courseID): int|string
    {
        $sql = "SELECT rating FROM rating WHERE userID = ? AND courseID = ?";
        $result = $this->executeQuery($sql, 'ii', array($userID, $courseID));
        return $result->fetch_assoc()["rating"] ?? " - No rating set!";
    }

    /**
     * Get a course by ID.
     * @param int $ID
     * @return array
     */
    function getCourse(int $ID): array
    {
        $sql = "SELECT * FROM courses WHERE courseID = ?;";
        $result = $this->executeQuery($sql, 'i', array($ID));
        return $this->fetchResults($result);
    }

    /**
     * Insert a course into a degree.
     * @param int $degreeID
     * @param int $courseID
     */
    function insertDegreeCourse(int $degreeID, int $courseID)
    {
        $sql = "INSERT INTO degrees_courses (degreeID, courseID) values(?, ?);";
        $this->insertOrUpdate($sql, 'ii', array($degreeID, $courseID));
    }

    /**
     * Delete a course from a degree.
     * @param int $degreeID
     * @param int $courseID
     */
    function deleteDegreeCourse(int $degreeID, int $courseID)
    {
        $sql = "DELETE FROM degrees_courses WHERE courseID = ? AND degreeID = ?;";
        $this->insertOrUpdate($sql, 'ii', array($courseID, $degreeID));
    }

    /**
     * Delete a course from a degree.
     * @param int $courseID
     * @return mixed
     */
    function getGraphData(int $courseID): mixed
    {
        $sql = "SELECT rating, COUNT(rating) as COUNT
            FROM rating
            WHERE courseID = ?
            GROUP BY rating DESC;";
        $result = $this->executeQuery($sql, 'i', array($courseID));
        return $result->fetch_all();
    }

    /**
     * Check if a given course exists in the user's active degree.
     * @param int $courseID
     * @return bool
     */
    function checkIfCourseExistsInActiveDegree(int $courseID): bool
    {
        $sql = "SELECT COUNT(*)
            FROM users
            JOIN degrees
            ON users.activeDegreeID = degrees.degreeID
            JOIN degrees_courses
            ON degrees_courses.degreeID = degrees.degreeID
            JOIN courses
            ON degrees_courses.courseID = courses.courseID
            WHERE courses.courseID = ? AND usersID = ?";

        $currentUser = Session::get(SESSION_USERID);
        $result = $this->executeQuery($sql, 'ii', array($courseID, $currentUser));
        $count = $result->fetch_assoc()["COUNT(*)"] ?? null;
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function updateCourse(array $body)
    {
        $sql = "UPDATE courses 
        SET name = ?, credits = ?, country = ?, city = ?, university = ?, description = ?, code = ?
        WHERE courseID = ?";

        $courseID = $body['courseID'];
        $name = $body['name'];
        $credits = $body['credits'];
        $country = $body['country'];
        $city = $body['city'];
        $university = $body['university'];
        $description = $body['description'];
        $code = $body['code'];

        $this->insertOrUpdate($sql, 'sdsssssi', array($name, $credits, $country, $city, $university, $description, $code, $courseID));
    }
}
