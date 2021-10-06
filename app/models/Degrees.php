<?php

namespace App\models;

use App\Core\Session;
use App\validation\DegreeValidator;
use App\validation\SharedValidation;

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
    public function validate(array $params): array
    {
        $errors = array();

        if (!SharedValidation::validName($params["name"])) {
            $errors[] = INVALID_NAME;
        }
        if (!DegreeValidator::validFieldOfStudy($params["field_of_study"])) {
            $errors[] = INVALID_FIELD_OF_STUDY;
        }
        if (!DegreeValidator::validDates($params["start_date"], $params["end_date"])) {
            $errors[] = INVALID_DATES;
        }
        if (!SharedValidation::validCountry($params["country"])){
            $errors[] = INVALID_COUNTRY;
        }
        if (!SharedValidation::validCity($params["city"])){
            $errors[] = INVALID_CITY;
        }
        if (!SharedValidation::validUniversity($params["university"])){
            $errors[] = INVALID_UNIVERSITY;
        }

        return $errors;
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
     * @param int $degreeID
     * @return bool
     */
    function updateDegree(array $params, int $userID, int $degreeID): bool
    {
        $sql = "UPDATE degrees SET name = ?, fieldOfStudy = ?, start_date = ?, end_date = ?, country = ?, city = ?, university = ? WHERE userID = ? AND degreeID = ?;";
        return $this->insertOrUpdate($sql, 'sssssssii', array($params["name"], $params["field_of_study"],
            $params["start_date"], $params["end_date"], $params["country"], $params["city"], $params["university"], $userID, $degreeID));
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
     * @param int $userID
     * @return array
     */
    function getLoggedInUserDegree(int $ID, int $userID): array
    {
        $sql = "SELECT * FROM degrees WHERE degreeID = ? AND userID = ?;";
        $result = $this->executeQuery($sql, 'ii', array($ID, $userID));
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
        $sql = "SELECT * 
                FROM degrees 
                LEFT JOIN users
                ON users.activeDegreeID = degrees.degreeID
                WHERE userID = ?
                ORDER BY activeDegreeID DESC;";
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

            if ($this->getActiveDegreeID($userID) == $degree->ID) {
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
     * @param int $userID
     * @return int|null
     */
    function getActiveDegreeID(int $userID): int|null
    {
        $sql = "SELECT activeDegreeID
            FROM users
            WHERE usersID = ?;";

        $result = $this->executeQuery($sql, 'i', array($userID));
        return $result->fetch_assoc()["activeDegreeID"] ?? null;
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
