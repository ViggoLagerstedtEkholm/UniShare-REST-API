<?php

namespace App\controllers;

use App\Core\Application;
use App\Core\Exceptions\GDResizeException;
use App\core\Exceptions\NotFoundException;
use App\Core\Request;
use App\core\Response;
use App\Core\Session;
use App\Core\ImageHandler;
use App\includes\ImageValidator;
use App\Models\Users;
use App\Models\Degrees;
use App\Models\Comments;
use App\Includes\Validate;
use App\Middleware\AuthenticationMiddleware;
use Google\ApiCore\ApiException;

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

    public function appendVisits(Request $request)
    {
        $body = $request->getBody();
        $profileID = $body['profileID'];
        $this->users->addVisitor($profileID);
        $userID = Session::get(SESSION_USERID);
        if($profileID === $userID){
            $this->users->addVisitDate($userID);
        }
    }

    /**
     * Get profile sidebar info.
     * @param Request $request
     * @return bool|string
     */
    public function getSideHUDInfo(Request $request): bool|string
    {
        $body = $request->getBody();
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

            return $this->jsonResponse($resp, 200);
        }
        return $this->jsonResponse("No such profile exists.", 404);
    }

    /**
     * This method resizes and uploads the image.
     * @throws GDResizeException
     * @throws ApiException
     */
    public function uploadImage(): bool|string
    {
        $response = new Response();

        $fileUploadName = 'file';
        $sessionID = Session::get(SESSION_USERID);

        if (ImageValidator::hasValidUpload($fileUploadName))
        {
            /*
            try{
                $stats = ImageValidator::checkImageForFeatures($fileUploadName);
            }catch(\Exception $e){
                return json_encode($e->getMessage());
            }
            */

            if (ImageValidator::hasInvalidImageExtension($fileUploadName))
            {
                return $response->setStatusCode(500);
            }

            $originalImage = $_FILES[$fileUploadName];
            $image_resize = $this->imageHandler->handleUploadResizing($originalImage);
            $this->users->uploadImage($image_resize, $sessionID);

            return $response->setStatusCode(200);
        } else {

            return $response->setStatusCode(500);
        }
    }

    /**
     * Delete comment.
     * @param Request $request
     * @return false|string
     */
    public function deleteComment(Request $request): bool|string
    {
        $body = $request->getBody();
        $commentID = $body['commentID'];

        $canRemove = $this->comments->checkIfUserAuthor(Session::get(SESSION_USERID), $commentID);

        if ($canRemove) {
            $this->comments->deleteComment($commentID);
            $resp = ['success' => true, 'data' => ['Status' => true, 'ID' => $commentID]];
            return $this->jsonResponse($resp, 200);
        } else {
            $resp = ['success' => true, 'data' => ['Status' => false, 'ID' => $commentID]];
            return $this->jsonResponse($resp, 500);
        }
    }

    /**
     * Add comment.
     * @param Request $request
     * @return false|string
     */
    public function addComment(Request $request): bool|string
    {
        $body = $request->getBody();

        $params = [
            'profileID' => $body['profileID'],
            'text' => $body['text'],
        ];

        $errors = $this->comments->validate($params);

        if (count($errors) > 0) {
            $resp = ['success' => false, 'data' => ['Status' => 'Invalid comment']];
            return $this->jsonResponse($resp, 500);
        }

        $posterID = Session::get(SESSION_USERID);
        $text = $body['text'];
        $profileID = $body['profileID'];

        $succeeded = $this->comments->addComment($posterID, $profileID, $text);
        if ($succeeded) {
            $resp = ['success' => true, 'data' => ['Status' => 'Added comment']];
            return $this->jsonResponse($resp, 200);
        } else {
            $resp = ['success' => false, 'data' => ['Status' => 'Error']];
            return $this->jsonResponse($resp, 500);
        }
    }

    /**
     * Remove course from degree.
     * @param Request $request
     * @return false|string
     */
    public function removeCourseFromDegree(Request $request): bool|string
    {
        $courseRequest = $request->getBody();

        $courseID = $courseRequest["courseID"];
        $degreeID = $courseRequest["degreeID"];

        $succeeded = $this->degrees->checkIfUserOwner(Session::get(SESSION_USERID), $degreeID);

        if ($succeeded) {
            $this->degrees->deleteCourseFromDegree($degreeID, $courseID);
            $resp = ['success' => true, 'data' => ['Status' => true, 'ID' => $courseID, 'degreeID' => $degreeID]];
            return $this->jsonResponse($resp, 200);
        } else {
            $resp = ['success' => false, 'data' => ['Status' => 'Error']];
            return $this->jsonResponse($resp, 403);
        }
    }
}
