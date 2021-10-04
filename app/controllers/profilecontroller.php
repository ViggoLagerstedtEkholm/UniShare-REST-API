<?php

namespace App\controllers;

use App\Core\Exceptions\GDResizeException;
use App\core\Handler;
use App\Core\ImageHandler;
use App\Core\Session;
use App\validation\ImageValidator;
use App\Middleware\AuthenticationMiddleware;
use App\Models\Comments;
use App\Models\Degrees;
use App\Models\Users;

/**
 * Profile controller for handling profiles.
 * @author Viggo Lagestedt Ekholm
 */
class ProfileController extends Controller
{
    private ImageHandler $imageHandler;
    private Users $users;
    private Degrees $degrees;
    private Comments $comments;

    public function __construct()
    {
        $this->setMiddlewares(new AuthenticationMiddleware(['uploadImage', 'removeCourseFromDegree', 'addComment']));

        $this->imageHandler = new ImageHandler();
        $this->users = new Users();
        $this->degrees = new Degrees();
        $this->comments = new Comments();
    }

    public function appendVisits(Handler $handler)
    {
        $body = $handler->getRequest()->getBody();
        $profileID = $body['profileID'];
        $this->users->addVisitor($profileID);
        if(Session::isLoggedIn()){
            $userID = Session::get(SESSION_USERID);
            if($profileID === $userID){
                $this->users->addVisitDate($userID);
            }
        }
    }

    /**
     * Get profile sidebar info.
     * @param Handler $handler
     * @return bool|string|null
     */
    public function getSideHUDInfo(Handler $handler): bool|string|null
    {
        $body = $handler->getRequest()->getBody();
        $ID = $body['profileID'];

        $user = $this->users->getUser($ID);
        if($user){
            $image = base64_encode($user["userImage"]);
            $firstname =  $user["userFirstName"];
            $lastname = $user["userLastName"];
            $username = $user["userDisplayName"];
            $privilege = $user["privilege"];
            $description = $user["description"];
            $joined = $user["joined"];
            $lastOnline = $user["lastOnline"];
            $visits = $user['visits'];

            $resp = ['success' => true, 'data' => [
                'image' => $image,
                'updatedVisitCount' => "test",
                'firstname' => $firstname,
                'lastname' => $lastname,
                'username' => $username,
                'privilege' => $privilege,
                'description' => $description,
                'lastOnline' => $lastOnline,
                'visits' => $visits,
                'joined' => $joined]];

            return $handler->getResponse()->jsonResponse($resp, 200);
        }
        $handler->getResponse()->setStatusCode(404);
        return null;
    }

    /**
     * This method resizes and uploads the image.
     * @throws GDResizeException
     */
    public function uploadImage(Handler $handler)
    {
        $fileUploadName = 'file';
        $sessionID = Session::get(SESSION_USERID);

        if (ImageValidator::hasValidUpload($fileUploadName))
        {
            if (!ImageValidator::hasValidImageExtension($fileUploadName))
            {
                $handler->getResponse()->setStatusCode(500);
            }

            $originalImage = $_FILES[$fileUploadName];
            $image_resize = $this->imageHandler->handleUploadResizing($originalImage);
            $this->users->uploadImage($image_resize, $sessionID);

            $handler->getResponse()->setStatusCode(200);
        } else {

            $handler->getResponse()->setStatusCode(500);
        }
    }

    /**
     * Delete comment.
     * @param Handler $handler
     */
    public function deleteComment(Handler $handler)
    {
        $body = $handler->getRequest()->getBody();
        $commentID = $body['commentID'];

        $canRemove = $this->comments->checkIfUserAuthor(Session::get(SESSION_USERID), $commentID);

        if ($canRemove) {
            $this->comments->deleteComment($commentID);
            $handler->getResponse()->setStatusCode(200);
        } else {
            $handler->getResponse()->setStatusCode(403);
        }
    }

    /**
     * Add comment.
     * @param Handler $handler
     * @return bool|string|null
     */
    public function addComment(Handler $handler): bool|string|null
    {
        $body = $handler->getRequest()->getBody();

        $params = [
            'profileID' => $body['profileID'],
            'text' => $body['text'],
        ];

        $errors = $this->comments->validate($params);

        if (count($errors) > 0) {
            return $handler->getResponse()->jsonResponse($errors, 422);
        }

        $posterID = Session::get(SESSION_USERID);
        $text = $body['text'];
        $profileID = $body['profileID'];

        $succeeded = $this->comments->addComment($posterID, $profileID, $text);

        if ($succeeded) {
            $handler->getResponse()->setStatusCode( 200);
        } else {
            $handler->getResponse()->setStatusCode(500);
        }
        return null;
    }

    /**
     * Remove course from degree.
     * @param Handler $handler
     */
    public function removeCourseFromDegree(Handler $handler)
    {
        $courseRequest = $handler->getRequest()->getBody();

        $courseID = $courseRequest["courseID"];
        $degreeID = $courseRequest["degreeID"];

        $succeeded = $this->degrees->checkIfUserOwner(Session::get(SESSION_USERID), $degreeID);

        if ($succeeded) {
            $this->degrees->deleteCourseFromDegree($degreeID, $courseID);
            $handler->getResponse()->setStatusCode(200);
        } else {
            $handler->getResponse()->setStatusCode(403);
        }
    }
}
