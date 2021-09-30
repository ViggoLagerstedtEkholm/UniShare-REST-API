<?php


namespace App\models;


use App\core\Session;
use Exception;

class Friends extends Database
{
    function isAlreadyFriends(int $otherID): bool
    {
        $userID = Session::get(SESSION_USERID);

        $sql = "SELECT * 
                FROM friends
                WHERE user_one = ? AND user_two = ?
                OR user_one = ? AND user_two = ?;";

        $result = $this->executeQuery($sql, 'iiii', array($userID, $otherID, $otherID, $userID));
        return !empty($result->fetch_assoc());
    }

    public function isRequestSender(int $otherID): bool
    {
        $userID = Session::get(SESSION_USERID);

        $sql = "SELECT * 
                FROM friend_request
                WHERE sender = ? AND receiver = ?;";

        $result = $this->executeQuery($sql, 'ii', array($userID, $otherID));
        return !empty($result->fetch_assoc());
    }

    public function isRequestReceiver(int $otherID): bool
    {
        $userID = Session::get(SESSION_USERID);

        $sql = "SELECT * 
                FROM friend_request
                WHERE sender = ? AND receiver = ?;";

        $result = $this->executeQuery($sql, 'ii', array($otherID, $userID));
        return !empty($result->fetch_assoc());
    }

    public function isRequestAlreadySent(int $otherID): bool
    {
        $userID = Session::get(SESSION_USERID);

        $sql = "SELECT * 
                FROM friend_request
                WHERE sender = ? AND receiver = ?
                OR sender = ? AND receiver = ?;";

        $result = $this->executeQuery($sql, 'iiii', array($userID, $otherID, $otherID, $userID));
        return !empty($result->fetch_assoc());
    }

    public function sendFriendRequest(int $otherID): bool
    {
        $userID = Session::get(SESSION_USERID);

        $sql = "INSERT INTO friend_request (sender, receiver) VALUES(?, ?);";

        return $this->insertOrUpdate($sql, 'ii', array($userID, $otherID));
    }

    public function rejectRequest(int $otherID)
    {
        $userID = Session::get(SESSION_USERID);

        $sql = "DELETE 
                FROM friend_request 
                WHERE sender = ? AND receiver = ?
                OR sender = ? AND receiver = ?";

        $this->delete($sql,'iiii', array($userID, $otherID, $otherID, $userID));
    }

    public function acceptRequest(int $otherID): bool
    {
        $userID = Session::get(SESSION_USERID);

        $accepted = false;

        $deleteQuery = "DELETE 
                        FROM friend_request 
                        WHERE sender = ? AND receiver = ?
                        OR sender = ? AND receiver = ?;";

        $insertQuery = "INSERT INTO friends (user_one, user_two) VALUES(?, ?);";

        try{
            $this->getConnection()->begin_transaction();

            $this->delete($deleteQuery, 'iiii', array($userID, $otherID, $otherID, $userID));
            $this->insertOrUpdate($insertQuery, 'ii', array($userID, $otherID));

            $accepted = true;
            $this->getConnection()->commit();
        }catch(Exception $e){
            $this->getConnection()->rollback();
        }
        return $accepted;
    }

    public function deleteFriend(int $otherID)
    {
        $userID = Session::get(SESSION_USERID);

        $sql = "DELETE 
                FROM friends 
                WHERE user_one = ? AND user_two = ?
                OR user_one = ? AND user_two = ?;";

        $this->delete($sql, 'iiii', array($userID, $otherID, $otherID, $userID));
    }

    public function getPendingReceivedRequests(): array
    {
        $userID = Session::get(SESSION_USERID);

        $sql = "SELECT sender, userDisplayName, userImage 
                FROM friend_request
                JOIN users 
                ON friend_request.sender = users.usersID
                WHERE receiver = ?;";

        $results = $this->executeQuery($sql, 'i', array($userID));
        return $this->fetchResults($results);
    }

    public function getPendingSentRequests(): array
    {
        $userID = Session::get(SESSION_USERID);

        $sql = "SELECT receiver, userDisplayName, userImage 
                FROM friend_request
                JOIN users 
                ON friend_request.receiver = users.usersID
                WHERE sender = ?;";

        $results = $this->executeQuery($sql, 'i', array($userID));
        return $this->fetchResults($results);
    }

    public function getFriends(int $userID): array
    {
        $sql = "SELECT * 
                FROM friends
                WHERE user_one = ?
                OR user_two = ?";

        $result = $this->executeQuery($sql, 'ii', array($userID, $userID));
        $friends = $this->fetchResults($result);

        $userArray = array();

        foreach($friends as $friendROW){
            $getUser = "SELECT usersID, userDisplayName, userImage FROM users WHERE usersID = ?;";
            if($friendROW['user_one'] == $userID){
                $result = $this->executeQuery($getUser, 'i', array($friendROW['user_two']));
            }else{
                $result = $this->executeQuery($getUser, 'i', array($friendROW['user_one']));
            }
            $userArray[] = $result->fetch_assoc();
        }

        return $userArray;
    }
}