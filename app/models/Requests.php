<?php

namespace App\models;

use App\core\Session;
use App\validation\RequestValidation;
use App\validation\SharedValidation;
use JetBrains\PhpStorm\Pure;
use Throwable;

/**
 * Model for handling course requests from users.
 * @author Viggo Lagestedt Ekholm
 */
class Requests extends Database implements IValidate
{
    /**
     * Check if the user input is sufficient enough.
     * @param array $params
     * @return array
     */
    public function validate(array $params): array
    {
        $errors = array();

        if(!SharedValidation::validName($params['name'])){
            $errors[] = INVALID_NAME;
        }
        if(!RequestValidation::validCredits($params['credits'])){
            $errors[] = INVALID_CREDITS;
        }
        if(!SharedValidation::validCountry($params['country'])){
            $errors[] = INVALID_CREDITS;
        }
        if(!SharedValidation::validCity($params['city'])){
            $errors[] = INVALID_CITY;
        }
        if(!SharedValidation::validUniversity($params['university'])){
            $errors[] = INVALID_UNIVERSITY;
        }
        if(!SharedValidation::validDescription($params['description'])){
            $errors[] = INVALID_UNIVERSITY;
        }

        return $errors;
    }

    /**
     * Insert a new requested course.
     * @param array $params
     * @param int $userID
     * @return bool
     */
    function insertRequestedCourse(array $params, int $userID): bool
    {
        date_default_timezone_set("Europe/Stockholm");
        $date = date('Y-m-d H:i:s');

        $sql = "INSERT INTO request (name, credits, country, city, isHandled, university, description, userID, date) 
                values(?,?,?,?,?,?,?,?,?);";
        return $this->insertOrUpdate($sql, 'sisssssis', array($params["name"], $params["credits"],
            $params["country"], $params["city"], false, $params["university"], $params["description"], $userID, $date));
    }

    /**
     * Check if the user is the owner of the course request.
     * @param int $userID
     * @param int $requestID
     * @return bool
     */
    function checkIfUserOwner(int $userID, int $requestID): bool
    {
        $sql = "SELECT requestID FROM request WHERE userID = ?;";
        $result = $this->executeQuery($sql, 'i', array($userID));

        while ($row = $result->fetch_array()) {
            if ($row["requestID"] == $requestID) {
                return true;
            }
        }
        return false;
    }

    /**
     * Delete a request by ID.
     * @param int $requestID
     */
    function deleteRequest(int $requestID)
    {
        $sql = "DELETE FROM request WHERE requestID = ?;";
        $this->executeQuery($sql, 'i', array($requestID));
    }

    /**
     * Deny a request by ID.
     * @param int $requestID
     * @return bool
     */
    function denyRequest(int $requestID): bool
    {
        $sql = "DELETE FROM request WHERE requestID = ?;";
        return $this->delete($sql, 'i', array($requestID));
    }

    /**
     * Transaction that adds the new request to the courses table and sets the request as handled.
     * @param int $requestID
     * @return bool
     * @throws Throwable
     */
    function approveRequest(int $requestID): bool
    {
        try {
            $this->getConnection()->begin_transaction();
            $this->getConnection()->rollback();

            $sql = "SELECT * FROM request WHERE requestID = ?;";
            $result = $this->executeQuery($sql, 'i', array($requestID));
            $request = $result->fetch_assoc();

            date_default_timezone_set("Europe/Stockholm");
            $date = date('Y-m-d H:i:s');

            $sql = "INSERT INTO courses (name, credits, added, country, city, university) values(?,?,?,?,?,?);";
            $inserted = $this->insertOrUpdate($sql, 'sissss', array($request["name"], $request["credits"], $date,
                $request["country"], $request["city"], $request["university"]));
            if (!$inserted) {
                $this->getConnection()->rollback();
            }

            $sql = "DELETE FROM request WHERE requestID = ?;";
            $deleted = $this->delete($sql, 'i', array($requestID));
            if (!$deleted) {
                $this->getConnection()->rollback();
            }

            $this->getConnection()->commit();

        } catch (Throwable $e) {
            $this->getConnection()->rollback();
            throw $e;
        }


        if ($inserted && $deleted) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get all requested courses that has not yet been handled.
     * @return array|null
     */
    function getRequestedCoursesFromUser(): array|null
    {
        $userID = Session::get(SESSION_USERID);
        $sql = "SELECT * FROM request WHERE isHandled = 0 AND userID = ?;";
        $result = $this->executeQuery($sql, 'i', array($userID));
        return $this->fetchResults($result);
    }

    function getRequestedCoursesAll(): array
    {
        $sql = "SELECT * FROM request WHERE isHandled = 0;";
        $result = $this->executeQuery($sql);
        return $this->fetchResults($result);
    }
}
