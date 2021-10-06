<?php

namespace App\models;

use App\core\Session;
use App\validation\ProjectValidator;
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
            $errors[] = INVALID_DESCRIPTION;
        }
        if(!RequestValidation::validCode($params['code'])){
            $errors[] = INVALID_COURSE_CODE;
        }
        if(!ProjectValidator::validURL($params['link'])){
            $errors[] = INVALID_COURSE_LINK;
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

        $sql = "INSERT INTO request (name, credits, country, city, university, description, userID, date, code, link) 
                values(?,?,?,?,?,?,?,?,?,?);";

        $name = $params["name"];
        $credits = $params["credits"];
        $country = $params["country"];
        $city = $params["city"];
        $university = $params["university"];
        $description = $params["description"];
        $code = $params["code"];
        $link = $params["link"];

        return $this->insertOrUpdate($sql, 'sdssssisss', array($name, $credits,
            $country, $city, $university, $description, $userID, $date, $code, $link));
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

            $name = $request["name"];
            $credits = $request["credits"];
            $country = $request["country"];
            $city = $request["city"];
            $university = $request["university"];
            $description = $request["description"];
            $code = $request["code"];
            $link = $request["link"];

            $sql = "INSERT INTO courses (name, credits, added, country, city, university, description, code, link) values(?,?,?,?,?,?,?,?,?);";
            $inserted = $this->insertOrUpdate($sql, 'sfsssssss',
                array(
                    $name,
                    $credits,
                    $date,
                    $country,
                    $city,
                    $university,
                    $description,
                    $code,
                    $link));
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
        $sql = "SELECT * FROM request WHERE userID = ?;";
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
