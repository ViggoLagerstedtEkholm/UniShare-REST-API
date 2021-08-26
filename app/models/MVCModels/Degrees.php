<?php

namespace App\models\MVCModels;

use App\Includes\Validate;
use App\Core\Session;
use JetBrains\PhpStorm\Pure;

/**
 * Model for handling degrees queries.
 * @author Viggo Lagestedt Ekholm
 */
class Degrees extends Database implements IValidate
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

        if (Validate::hasInvalidDates($params["start_date"], $params["end_date"]) === true) {
            $errors[] = INVALID_DATES;
        }

        return $errors;
    }

    /**
     * Check if the new degree ID exists for the current user.
     * @param int $newActiveDegreeID
     * @return bool
     */
    function userHasDegreeID(int $newActiveDegreeID): bool
    {
        $sql = "SELECT degreeID FROM degrees
            JOIN users
            ON degrees.userID = users.usersID
            WHERE usersID = ?;";

        $ID = Session::get(SESSION_USERID);

        $result = $this->executeQuery($sql, 'i', array($ID));

        $IDs = array();
        while ($row = $result->fetch_array()) {
            $IDs[] = $row["degreeID"];
        }

        $exists = in_array($newActiveDegreeID, $IDs);

        if ($exists) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Delete course from degree.
     * @param int $degreeID
     * @param int $courseID
     */
    function deleteCourseFromDegree(int $degreeID, int $courseID)
    {
        $sql = "DELETE FROM degrees_courses
            WHERE degreeID = ? AND courseID = ?;";

        $this->executeQuery($sql, 'ii', array($degreeID, $courseID));
    }

    /**
     * Check if the current user own a specific degree.
     * @param int $userID
     * @param string $degreeID
     * @return bool
     */
    function checkIfUserOwner(int $userID, string $degreeID): bool
    {
        $sql = "SELECT degreeID FROM degrees
            JOIN users
            WHERE userID = ?;";
        $result = $this->executeQuery($sql, 'i', array($userID));

        while ($row = $result->fetch_array()) {
            if ($row["degreeID"] == $degreeID) {
                return true;
            }
        }
        return false;
    }

    /**
     * Upload a degree.
     * @param array $params
     * @param int $ID
     */
    function uploadDegree(array $params, int $ID)
    {
        $sql = "INSERT INTO degrees (name, fieldOfStudy, userID, start_date, end_date, country, city, university) values(?,?,?,?,?,?,?,?);";
        $this->insertOrUpdate($sql, 'ssisssss', array($params["name"], $params["field_of_study"], $ID,
            $params["start_date"], $params["end_date"], $params["country"], $params["city"], $params["university"]));
    }

    /**
     * Update a degree.
     * @param array $params
     * @param int $userID
     * @return bool
     */
    function updateDegree(array $params, int $userID): bool
    {
        $sql = "UPDATE degrees SET name = ?, fieldOfStudy = ?, start_date = ?, end_date = ?, country = ?, city = ?, university = ? WHERE userID = ?;";
        return $this->insertOrUpdate($sql, 'sssssssi', array($params["name"], $params["field_of_study"],
            $params["start_date"], $params["end_date"], $params["country"], $params["city"], $params["university"], $userID));
    }

    /**
     * Delete a degree.
     * @param int $ID
     */
    function deleteDegree(int $ID)
    {
        $sql = "DELETE FROM degrees WHERE degreeID = ?;";
        $this->executeQuery($sql, 'i', array($ID));
    }

    /**
     * Get a degree.
     * @param int $ID
     * @return array
     */
    function getDegree(int $ID): array
    {
        $sql = "SELECT * FROM degrees WHERE degreeID = ?;";
        $result = $this->executeQuery($sql, 'i', array($ID));
        return $this->fetchResults($result);
    }

    /**
     * Get all courses in degree.
     * @param $degreeID
     * @return array
     */
    function getCoursesDegree($degreeID): array
    {
        $sql = "SELECT * FROM courses
            JOIN degrees_courses
            ON courses.courseID = degrees_courses.courseID
            WHERE degreeID = ?;";

        $result = $this->executeQuery($sql, 'i', array($degreeID));
        return $this->fetchResults($result);
    }

    /**
     * Get all degrees from user ID.
     * @param int $userID
     * @return array
     */
    function getDegrees(int $userID): array
    {
        $sql = "SELECT * FROM degrees WHERE userID = ?;";
        $result = $this->executeQuery($sql, 'i', array($userID));

        $degrees = array();
        while ($row = $result->fetch_assoc()) {
            $degree = new Degree();
            $degree->ID = $row["degreeID"];
            $degree->name = $row["name"];
            $degree->field_of_study = $row["fieldOfStudy"];
            $degree->start_date = $row["start_date"];
            $degree->end_date = $row["end_date"];
            $degree->country = $row["country"];
            $degree->city = $row["city"];
            $degree->university = $row["university"];

            $courses = $this->getCoursesDegree($degree->ID);
            $totalCredits = $this->getTotalDegreeCredits($degree->ID);

            if ($this->getActiveDegreeID() == $degree->ID) {
                $degree->isActiveDegree = true;
            }

            $degree->courses = $courses;
            $degree->totalCredits = $totalCredits;
            $degrees[] = $degree;
        }
        return $degrees;
    }

    /**
     * Get all active degree ID from the current user.
     * @return int|null
     */
    function getActiveDegreeID(): int|null
    {
        $sql = "SELECT activeDegreeID
            FROM users
            WHERE usersID = ?;";

        $userID = Session::get(SESSION_USERID);
        $result = $this->executeQuery($sql, 'i', array($userID));
        return $result->fetch_assoc()["activeDegreeID"];
    }

    /**
     * Get total amounts of credits from degree.
     * @param int $degreeID
     * @return string|null
     */
    function getTotalDegreeCredits(int $degreeID): string|null
    {
        $sql = "SELECT SUM(credits)
            FROM courses
            JOIN degrees_courses
            ON courses.courseID = degrees_courses.courseID
            JOIN degrees
            ON degrees_courses.degreeID = degrees.degreeID
            WHERE degrees.degreeID = ?;";

        $result = $this->executeQuery($sql, 'i', array($degreeID));
        return $result->fetch_assoc()["SUM(credits)"];
    }
}
