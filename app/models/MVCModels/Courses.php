<?php

namespace App\models\MVCModels;

use App\Core\Session;
use App\Includes\Validate;
use JetBrains\PhpStorm\Pure;

/**
 * Model for handling courses.
 * @author Viggo Lagestedt Ekholm
 */
class Courses extends Database implements IValidate
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
     * Get the amount of total courses in the courses table.
     * @return int count.
     */
    function getCoursesCount(): int
    {
        $sql = "SELECT Count(*) FROM courses";
        $result = $this->executeQuery($sql);
        return $result->fetch_assoc()["Count(*)"];
    }

    /**
     * Get the amount of total courses in the courses table from a given search.
     * @param string $search
     * @return int count.
     */
    function getCourseCountSearch(string $search): int
    {
        $MATCH = $this->builtMatchQuery('courses', $search, 'courseID');
        $sql = "SELECT Count(*) FROM courses WHERE $MATCH";
        $result = $this->executeQuery($sql);
        return $result->fetch_assoc()["Count(*)"];
    }

    /**
     * Apply the filters to get an array of courses that matches the filter.
     * @param int $from
     * @param int $to
     * @param string $option
     * @param string $filterOrder
     * @param string|null $search
     * @return array courses.
     */
    function fetchCoursesSearch(int $from, int $to, ?string $option, ?string $filterOrder, string $search = null): array
    {
        $option ?? $option = "name";
        $filterOrder ?? $filterOrder = "DESC";

        if (!is_null($search)) {
            $MATCH = $this->builtMatchQuery('courses', $search, 'courseID');
            $searchQuery = "SELECT AVG(rating) AS average_rating, courses.*
                      FROM rating
                      RIGHT JOIN courses
                      ON rating.courseID = courses.courseID
                      WHERE $MATCH
                      GROUP BY courses.courseID
                      ORDER BY $option $filterOrder
                      LIMIT ?, ?;";

            $result = $this->executeQuery($searchQuery, 'ii', array($from, $to));
        } else {
            $searchQuery = "SELECT AVG(rating) AS average_rating, courses.*
                      FROM rating
                      RIGHT JOIN courses
                      ON rating.courseID = courses.courseID
                      GROUP BY courses.courseID
                      ORDER BY $option $filterOrder
                      LIMIT ?, ?;";

            $result = $this->executeQuery($searchQuery, 'ii', array($from, $to));
        }

        $courses = array();
        while ($row = $result->fetch_array()) {
            $course = new Course();
            $course->ID = $row['courseID'];
            $course->name = $row['name'];
            $course->credits = $row['credits'];
            $course->duration = $row['duration'];
            $course->added = $row['added'];
            $course->country = $row['country'];
            $course->city = $row['city'];
            $course->university = $row['university'];
            $course->rating = $row['average_rating'] ?? "No ratings";
            if (Session::isLoggedIn()) {
                $course->existsInActiveDegree = $this->checkIfCourseExistsInActiveDegree($course->ID);
            }
            $courses[] = $course;
        }
        return $courses;
    }

    /**
     * Get the top 10 highest rated courses.
     * @return array courses.
     */
    function getTOP10Courses(): array
    {
        $sql = "SELECT AVG(rating) AS average_rating, courses.*
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
     * @return mixed
     */
    function getPopularityRank(int $courseID): mixed
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
     * @return mixed
     */
    function getOverallRankingRating(int $courseID): mixed
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
     * Insert a course.
     * @param array $params
     * @return bool
     */
    function insertCourse(array $params): bool
    {
        $sql = "INSERT INTO courses (name, credits, duration, added, country, city, university) values(?,?,?,?,?,?,?);";
        date_default_timezone_set("Europe/Stockholm");
        $date = date("Y-m-d", time());
        return $this->insertOrUpdate($sql, 'ssssss', array($params["name"], $params["credits"], $params["duration"], $date, $params["fieldOfStudy"], $params["location"]));
    }

    //TODO
    function deleteCourse()
    {
        //TODO
    }

    /**
     * Get the arithmetic mean score of a given course.
     * @param int $courseID
     * @return array
     */
    function getArithmeticMeanScore(int $courseID): array
    {
        $sql = "SELECT AVG(rating), COUNT(rating) FROM rating WHERE courseID = ?;";
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
}
