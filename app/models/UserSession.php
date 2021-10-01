<?php

namespace App\Models;

use App\Core\Cookie;
use App\Core\Session;

/**
 * Model for handling user sessions.
 * @author Viggo Lagestedt Ekholm
 */
class userSession extends Database
{
    /**
     * Delete existing session.
     * @param int $ID
     * @param string $user_agent
     */
    public function deleteExistingSession(int $ID, string $user_agent)
    {
        $sql = "DELETE FROM sessions WHERE userID = ? AND userAgent = ?";
        $this->delete($sql, 'ii', array($ID, $user_agent));
    }

    /**
     * Insert session.
     * @param int $ID
     * @param string $user_agent
     * @param string $hash
     */
    public function insertSession(int $ID, string $user_agent, string $hash)
    {
        $sql = "INSERT INTO sessions (userID, userAgent, session) values(?, ?, ?);";
        $this->insertOrUpdate($sql, 'iss', array($ID, $user_agent, $hash));
    }

    /**
     * Get session from cookie.
     * @return array|null
     */
    public function getSessionFromCookie(): array|null
    {
        if (Cookie::exists(REMEMBER_ME_COOKIE_NAME)) {
            $sql = "SELECT * FROM sessions WHERE userAgent = ? AND session = ? LIMIT 1";

            $user_agent = Session::agent_no_version();
            $session = Cookie::get(REMEMBER_ME_COOKIE_NAME);

            $result = $this->executeQuery($sql, 'ss', array($user_agent, $session));
            return $result->fetch_assoc();
        }
        return null;
    }
}
