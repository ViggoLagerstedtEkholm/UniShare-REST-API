<?php

namespace App\Models\MVCModels;

use App\Core\Session;
use App\Core\Cookie;

/**
 * Model for handling users.
 * @author Viggo Lagestedt Ekholm
 */
class Users extends Database
{
    /**
     * Get user count.
     * @return ?int
     */
    function getUserCount(): ?int
    {
        $sql = "SELECT Count(*) FROM users";
        $result = $this->executeQuery($sql);
        return $result->fetch_assoc()["Count(*)"];
    }

    /**
     * Get user search count.
     * @param string $search
     * @return ?int
     */
    function getUserCountSearch(string $search): ?int
    {
        $MATCH = $this->builtMatchQuery('users', $search, 'usersID');
        $sql = "SELECT Count(*) FROM users WHERE $MATCH";
        $result = $this->executeQuery($sql);
        return $result->fetch_assoc()["Count(*)"];
    }

    /**
     * Get users from search.
     * @param int $from
     * @param int $to
     * @param string|null $option
     * @param string|null $filterOrder
     * @param string|null $search
     * @return array|null
     */
    function fetchPeopleSearch(int $from, int $to, ?string $option, ?string $filterOrder, string $search = null): array|null
    {
        $option ?? $option = "userDisplayName";
        $filterOrder ?? $filterOrder = "DESC";
        if (!is_null($search)) {
            $MATCH = $this->builtMatchQuery('users', $search, 'usersID');

            $searchQuery = "SELECT *
                      FROM users
                      WHERE $MATCH
                      ORDER BY $option $filterOrder
                      LIMIT ?, ?;";

            $result = $this->executeQuery($searchQuery, 'ii', array($from, $to));
        } else {
            $searchQuery = "SELECT *
                     FROM users
                     ORDER BY $option $filterOrder
                     LIMIT ?, ?;";

            $result = $this->executeQuery($searchQuery, 'ii', array($from, $to));
        }

        return $this->fetchResults($result);
    }

    /**
     * Check if a user exists given a criteria.
     * @param string $attribute
     * @param mixed $value
     * @return bool|array|null
     */
    function userExists(string $attribute, mixed $value): bool|array|null
    {
        $sql = "SELECT * FROM users WHERE $attribute = ?;";
        $result = $this->executeQuery($sql, 's', array($value));
        $row = $result->fetch_assoc();
        if (is_null($row)) {
            return null;
        } else {
            return $row;
        }
    }

    /**
     * Update a user.
     * @param string $attribute
     * @param mixed $value
     * @param int $ID
     */
    function updateUser(string $attribute, mixed $value, int $ID)
    {
        $sql = "UPDATE users SET $attribute = ? WHERE usersID = ?;";
        $this->insertOrUpdate($sql, 'si', array($value, $ID));
    }

    /**
     * Terminate a user.
     * @param int $userID
     */
    function terminateAccount(int $userID)
    {
        $sql = "DELETE FROM users WHERE usersID = ?;";
        $this->executeQuery($sql, 'i', array($userID));
    }

    /**
     * Get a user.
     * @param int $ID
     * @return bool|array|null
     */
    function getUser(int $ID): bool|array|null
    {
        $sql = "SELECT * FROM users WHERE usersID = ?;";

        $result = $this->executeQuery($sql, 'i', array($ID));

        if ($row = $result->fetch_assoc()) {
            return $row;
        } else {
            return false;
        }
    }

    /**
     * Logout a user.
     */
    function logout()
    {
        $userSession = new UserSession();
        $session = $userSession->getSessionFromCookie();
        $ID = Session::get(SESSION_USERID);
        $user_agent = Session::agent_no_version();

        if (!empty($session)) {
            $userSession->deleteExistingSession($ID, $user_agent);
        }

        Session::delete(SESSION_USERID);
        Session::delete(SESSION_MAIL);
        Session::delete(SESSION_PRIVILEGE);

        if (Cookie::exists(REMEMBER_ME_COOKIE_NAME)) {
            Cookie::delete(REMEMBER_ME_COOKIE_NAME);
        }
    }

    /**
     * Upload a image.
     * @param mixed $image
     * @param int $ID
     */
    function uploadImage(mixed $image, int $ID)
    {
        $sql = "UPDATE users SET userImage =? WHERE usersID = ?;";
        $this->insertOrUpdate($sql, 'si', array($image, $ID));
    }

    /**
     * Add a visitor and return the updated amount.
     * @param int $ID
     * @param array $user
     * @return int
     */
    function addVisitor(int $ID, array $user): int
    {
        $visits = $user["visits"];
        $updatedVisits = $visits + 1;
        $sql = "UPDATE users SET visits =? WHERE usersID = ?;";
        $this->insertOrUpdate($sql, 'ii', array($updatedVisits, $ID));
        return $updatedVisits;
    }

    /**
     * Add a visit date for a user and get the updated date.
     * @param int $ID
     * @return string
     */
    function addVisitDate(int $ID): string
    {
        $sql = "UPDATE users SET lastOnline = ? WHERE usersID = ?;";

        date_default_timezone_set("Europe/Stockholm");
        $date = date('Y-m-d H:i:s');

        $this->insertOrUpdate($sql, 'si', array($date, $ID));
        return $date;
    }
}
