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