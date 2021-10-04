<?php

namespace App\models;

use App\Core\Session;
use App\Core\Cookie;

/**
 * Model for handling users.
 * @author Viggo Lagestedt Ekholm
 */
class Users extends Database
{
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

    function setVerified(string $email): bool
    {
        $sql = "UPDATE users SET isVerified = 1 WHERE userEmail = ?";
        return $this->insertOrUpdate($sql,'s', array($email));
    }

    function verifyUser(string $email): array
    {
        $sql = "SELECT verificationHash
                FROM users
                WHERE userEmail = ?;";

        $results = $this->executeQuery($sql, 's', array($email));
        return $this->fetchResults($results);
    }

    function getUsersCount(): int
    {
        $sql = "SELECT Count(*)
               FROM users;";

        $result = $this->executeQuery($sql);
        return $result->fetch_assoc()['Count(*)'];
    }

    function suspend(int $userID): bool
    {
        $sql = "UPDATE users SET isSuspended = 1 WHERE usersID = ?;";
        return $this->insertOrUpdate($sql, 'i', array($userID));
    }

    public function enable(mixed $userID): bool
    {
        $sql = "UPDATE users SET isSuspended = 0 WHERE usersID = ?;";
        Session::delete($userID);
        return $this->insertOrUpdate($sql, 'i', array($userID));
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
     * @return bool
     */
    function terminateAccount(int $userID): bool
    {
        $sql = "DELETE FROM users WHERE usersID = ?;";
        Session::delete($userID);
        return $this->delete($sql, 'i', array($userID));
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
     */
    function addVisitor(int $ID)
    {
        $user = $this->getUser($ID);
        $visits = $user["visits"] ?? 0;
        $updatedVisits = $visits + 1;
        $sql = "UPDATE users SET visits = ? WHERE usersID = ?;";
        $this->insertOrUpdate($sql, 'ii', array($updatedVisits, $ID));
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
