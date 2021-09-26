<?php


namespace App\controllers;


use App\core\Request;
use App\models\Friends;

class FriendsController extends Controller
{
    protected Friends $friends;

    function __construct()
    {
        $this->friends = new Friends;
    }

    // CHECK IF ALREADY FRIENDS
    function isFriendsWith(Request $request): bool|string|null
    {
        $body = $request->getBody();
        $otherID = $body['otherID'];
        $isFriendWith = $this->friends->isAlreadyFriends($otherID);
        return $this->jsonResponse($isFriendWith, 200);
    }

    //  IF I AM THE REQUEST SENDER
    function ifSender(Request $request): bool|string|null
    {
        $body = $request->getBody();
        $otherID = $body['otherID'];

        $isRequestSender = $this->friends->isRequestSender($otherID);
        return $this->jsonResponse($isRequestSender, 200);
    }

    //  IF I AM THE RECEIVER
    function ifReceiver(Request $request): bool|string|null
    {
        $body = $request->getBody();
        $otherID = $body['otherID'];

        $isRequestReceiver = $this->friends->isRequestReceiver($otherID);
        return $this->jsonResponse($isRequestReceiver, 200);
    }

    // CHECK IF REQUEST HAS ALREADY BEEN SENT
    function isRequestSent(Request $request): bool|string|null
    {
        $body = $request->getBody();
        $otherID = $body['otherID'];

        $isRequestAlreadySent = $this->friends->isRequestAlreadySent($otherID);
        return $this->jsonResponse($isRequestAlreadySent, 200);
    }

    // MAKE PENDING FRIENDS (SEND FRIEND REQUEST)
    function request(Request $request): bool|string|null
    {
        $body = $request->getBody();
        $otherID = $body['otherID'];

        if(!$this->friends->isAlreadyFriends($otherID) && !$this->friends->isRequestAlreadySent($otherID) && !$this->isRequestSent($otherID))
        {
            $this->friends->sendFriendRequest($otherID);
            return $this->jsonResponse("Request sent", 200);
        }
        return $this->jsonResponse("Error", 500);
    }

    // CANCEL FRIEND REQUEST
    function reject(Request $request){
        $body = $request->getBody();
        $otherID = $body['otherID'];

        $this->friends->rejectRequest($otherID);
    }

    // MAKE FRIENDS
    function accept(Request $request){
        $body = $request->getBody();
        $otherID = $body['otherID'];

        $this->friends->acceptRequest($otherID);
    }

    // DELETE FRIENDS
    function delete(Request $request){
        $body = $request->getBody();
        $otherID = $body['otherID'];

        $this->friends->deleteFriend($otherID);
    }

    // REQUEST NOTIFICATIONS
    function getMyPendingReceivedRequest(): bool|string|null
    {
        $requests = $this->friends->getPendingReceivedRequests();

        $data = array();
        foreach($requests as $key => $value){
            $data[$key] = $value;
            $data[$key]['userImage'] = base64_encode($value['userImage']);
        }

        return $this->jsonResponse($data, 200);
    }

    // REQUEST NOTIFICATIONS
    function getMyPendingSentRequest(): bool|string|null
    {
        $requests = $this->friends->getPendingSentRequests();

        $data = array();
        foreach($requests as $key => $value){
            $data[$key] = $value;
            $data[$key]['userImage'] = base64_encode($value['userImage']);
        }

        return $this->jsonResponse($data, 200);
    }

    // GET FRIENDS
    function getFriends(): bool|string|null
    {
        $friends = $this->friends->getFriends();
        return $this->jsonResponse($friends, 200);
    }
}