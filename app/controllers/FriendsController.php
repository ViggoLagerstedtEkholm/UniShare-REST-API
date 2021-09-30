<?php


namespace App\controllers;


use App\core\Handler;
use App\middleware\AuthenticationMiddleware;
use App\models\Friends;

class FriendsController extends Controller
{
    protected Friends $friends;

    function __construct()
    {
        $this->setMiddlewares(new AuthenticationMiddleware(
            ['isFriendsWith',
                'ifSender',
                'ifReceiver',
                'isRequestSent',
                'request',
                'reject',
                'accept',
                'delete',
                'getMyPendingReceivedRequest',
                'getMyPendingSentRequest',
                ]));

        $this->friends = new Friends;
    }

    // CHECK IF ALREADY FRIENDS
    function isFriendsWith(Handler $handler): bool|string|null
    {
        $body = $handler->getRequest()->getBody();
        $otherID = $body['otherID'];
        $isFriendWith = $this->friends->isAlreadyFriends($otherID);
        return $handler->getResponse()->jsonResponse($isFriendWith, 200);
    }

    //  IF I AM THE REQUEST SENDER
    function ifSender(Handler $handler): bool|string|null
    {
        $body = $handler->getRequest()->getBody();
        $otherID = $body['otherID'];

        $isRequestSender = $this->friends->isRequestSender($otherID);
        return $handler->getResponse()->jsonResponse($isRequestSender, 200);
    }

    //  IF I AM THE RECEIVER
    function ifReceiver(Handler $handler): bool|string|null
    {
        $body = $handler->getRequest()->getBody();
        $otherID = $body['otherID'];

        $isRequestReceiver = $this->friends->isRequestReceiver($otherID);
        return $handler->getResponse()->jsonResponse($isRequestReceiver, 200);
    }

    // CHECK IF REQUEST HAS ALREADY BEEN SENT
    function isRequestSent(Handler $handler): bool|string|null
    {
        $body = $handler->getRequest()->getBody();
        $otherID = $body['otherID'];

        $isRequestAlreadySent = $this->friends->isRequestAlreadySent($otherID);
        return $handler->getResponse()->jsonResponse($isRequestAlreadySent, 200);
    }

    // MAKE PENDING FRIENDS (SEND FRIEND REQUEST)
    function request(Handler $handler): bool|string|null
    {
        $body = $handler->getRequest()->getBody();
        $otherID = $body['otherID'];

        if(!$this->friends->isAlreadyFriends($otherID) && !$this->friends->isRequestAlreadySent($otherID))
        {
            $this->friends->sendFriendRequest($otherID);
            return $handler->getResponse()->jsonResponse("Request sent", 200);
        }
        return $handler->getResponse()->jsonResponse("Error", 500);
    }

    // CANCEL FRIEND REQUEST
    function reject(Handler $handler){
        $body = $handler->getRequest()->getBody();
        $otherID = $body['otherID'];

        $this->friends->rejectRequest($otherID);
    }

    // MAKE FRIENDS
    function accept(Handler $handler){
        $body = $handler->getRequest()->getBody();
        $otherID = $body['otherID'];

        $this->friends->acceptRequest($otherID);
    }

    // DELETE FRIENDS
    function delete(Handler $handler){
        $body = $handler->getRequest()->getBody();
        $otherID = $body['otherID'];

        $this->friends->deleteFriend($otherID);
    }

    // REQUEST NOTIFICATIONS
    function getMyPendingReceivedRequest(Handler $handler): bool|string|null
    {
        $requests = $this->friends->getPendingReceivedRequests();

        $data = array();
        foreach($requests as $key => $value){
            $data[$key] = $value;
            $data[$key]['userImage'] = base64_encode($value['userImage']);
        }

        return $handler->getResponse()->jsonResponse($data, 200);
    }

    // REQUEST NOTIFICATIONS
    function getMyPendingSentRequest(Handler $handler): bool|string|null
    {
        $requests = $this->friends->getPendingSentRequests();

        $data = array();
        foreach($requests as $key => $value){
            $data[$key] = $value;
            $data[$key]['userImage'] = base64_encode($value['userImage']);
        }

        return $handler->getResponse()->jsonResponse($data, 200);
    }

    // GET FRIENDS
    function getFriends(Handler $handler): bool|string|null
    {
        $body = $handler->getRequest()->getBody();
        $ID = $body['ID'];
        $friends = $this->friends->getFriends($ID);
        return $handler->getResponse()->jsonResponse($friends, 200);
    }
}