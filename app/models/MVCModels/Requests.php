<?php

namespace App\models\MVCModels;

use App\Includes\Validate;
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
    #[Pure] public function validate(array $params): array
    {
        $errors = array();
        if (Validate::arrayHasEmptyValue($params) === true) {
            $errors[] = EMPTY_FIELDS;
        }

        if (!is_numeric($params["credits"]) || !is_numeric($params["duration"])) {
            $errors[] = NOTNUMERIC;
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
        $sql = "INSERT INTO request (name, credits, duration, country, city, isHandled, university, description, userID) values(?,?,?,?,?,?,?,?,?);";
        return $this->insertOrUpdate($sql, 'siisssisi', array($params["name"], $params["credits"], $params["duration"], $params["country"], $params["city"], false, $params["university"], $params["description"], $userID));
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
        $sql = "UPDATE request SET isHandled = 1 WHERE requestID = ?;";
        return $this->insertOrUpdate($sql, 'i', array($requestID));
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

            $sql = "INSERT INTO courses (name, credits, duration, added, country, city, university) values(?,?,?,?,?,?,?);";
            $inserted = $this->insertOrUpdate($sql, 'siissss', array($request["name"], $request["credits"], $request["duration"], $date, $request["country"], $request["city"], $request["university"]));
            if (!$inserted) {
                $this->getConnection()->rollback();
            }

            $sql = "UPDATE request SET isHandled = 1 WHERE requestID = ?;";
            $updated = $this->insertOrUpdate($sql, 'i', array($requestID));
            if (!$updated) {
                $this->getConnection()->rollback();
            }

            $this->getConnection()->commit();

        } catch (Throwable $e) {
            $this->getConnection()->rollback();
            throw $e;
        }


        if ($inserted && $updated) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get all requested courses that has not yet been handled.
     * @return array|null
     */
    function getRequestedCourses(): array|null
    {
        $sql = "SELECT * FROM request WHERE isHandled = 0;";
        $result = $this->executeQuery($sql);
        return $this->fetchResults($result);
    }
}
