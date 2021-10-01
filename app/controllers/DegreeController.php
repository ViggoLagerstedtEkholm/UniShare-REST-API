<?php

namespace App\controllers;
use App\core\Handler;
use App\Core\Session;
use App\Middleware\AuthenticationMiddleware;
use App\models\Courses;
use App\Models\Degrees;
use App\models\Users;

/**
 * Degree controller for handling degrees.
 * @author Viggo Lagestedt Ekholm
 */
class DegreeController extends Controller
{
    private Degrees $degrees;
    private Users $users;
    private Courses $courses;

    public function __construct()
    {
        $this->setMiddlewares(new AuthenticationMiddleware(['uploadDegree', 'deleteDegree', 'updateDegree', 'getdegrees', 'toggleCourseToDegree', 'getDegreesSettings']));

        $this->degrees = new Degrees();
        $this->users = new Users();
        $this->courses = new Courses();
    }

    /**
     * This method handles uploading a degree to a user.
     * @param Handler $handler
     * @return bool|string|null
     */
    public function uploadDegree(Handler $handler): bool|string|null
    {
        $body = $handler->getRequest()->getBody();

        $params = [
            "name" => $body["name"],
            "field_of_study" => $body["field_of_study"],
            "start_date" => $body["start_date"],
            "end_date" => $body["end_date"],
            "country" => $body["country"],
            "city" => $body["city"],
            "university" => $body["university"],
        ];

        $errors = $this->degrees->validate($params);

        if (count($errors) > 0) {
            return $handler->getResponse()->jsonResponse($errors, 500);
        }

        $this->degrees->uploadDegree($params, Session::get(SESSION_USERID));
        return $handler->getResponse()->jsonResponse(true, 200);
    }

    /**
     * This method gets the degrees from the logged in user.
     * @param Handler $handler
     * @return false|string
     */
    public function getDegrees(Handler $handler): false|string
    {
        $body = $handler->getRequest()->getBody();
        $ID = $body['profileID'];
        $degrees = $this->degrees->getDegrees($ID);
        $resp = ['degrees' => $degrees];
        return $handler->getResponse()->jsonResponse($resp, 200);
    }

    public function getActiveDegreeID(Handler $handler): bool|string|null
    {
        $ID = Session::get(SESSION_USERID);
        $activeDegreeID = $this->degrees->getActiveDegreeID($ID);
        return $handler->getResponse()->jsonResponse($activeDegreeID, 200);
    }

    public function getDegreesSettings(Handler $handler): bool|string|null
    {
        $ID = Session::get(SESSION_USERID);
        $degrees = $this->degrees->getDegrees($ID);

        $names = array();
        foreach($degrees as $degree){
            $names[] = ['name' => $degree->name, 'isActive' => $degree->isActiveDegree, 'degreeID' => $degree->ID];
        }
        return $handler->getResponse()->jsonResponse($names, 200);
    }

    /**
     * This method gets a specific degree from ID from the logged in user.
     * @param Handler $handler
     * @return false|string
     */
    public function getDegree(Handler $handler): false|string
    {
        $body = $handler->getRequest()->getBody();
        $degreeID = $body["degreeID"];

        if (!empty($degreeID)) {
            $degree = $this->degrees->getDegree($degreeID);
        } else {
            $resp = ['success' => false, 'status' => 'No matching ID!'];
            return $handler->getResponse()->jsonResponse($resp, 404);
        }

        if (!is_null($degree)) {
            $resp = ['success' => true, 'data' => ['degree' => $degree[0]]];
            return $handler->getResponse()->jsonResponse($resp, 200);
        } else {
            $resp = ['success' => false];
            return $handler->getResponse()->jsonResponse($resp, 500);
        }
    }

    /**
     * This method updates a specific degree from ID from the logged in user.
     * @param Handler $handler
     * @return bool|string|null
     */
    public function updateDegree(Handler $handler): bool|string|null
    {
        $body = $handler->getRequest()->getBody();
        $degreeID = $body["degreeID"];

        $params = [
            'name' => $body["name"],
            'field_of_study' => $body["field_of_study"],
            'start_date' => $body["start_date"],
            'end_date' => $body["end_date"],
            'country' => $body["country"],
            'city' => $body["city"],
            'university' => $body["university"],
        ];

        $userID = Session::get(SESSION_USERID);

        $errors = $this->degrees->validate($params);

        if (count($errors) > 0) {
            return $handler->getResponse()->jsonResponse($errors, 500);
        }

        $canUpdate = $this->degrees->checkIfUserOwner($userID, $degreeID);

        if ($canUpdate) {
            $this->degrees->updateDegree($body, $userID);
            $handler->getResponse()->setStatusCode(200);
        } else {
            $handler->getResponse()->setStatusCode(401);
        }
        return null;
    }

    /**
     * This method deletes a specific degree from ID from the logged in user.
     * @param Handler $handler
     * @return false|string
     */
    public function deleteDegree(Handler $handler): bool|string
    {
        $body = $handler->getRequest()->getBody();
        $degreeID = $body['degreeID'];
        $userID = Session::get(SESSION_USERID);

        $canDelete = $this->degrees->checkIfUserOwner($userID, $degreeID);

        if ($canDelete) {
            $this->degrees->deleteDegree($degreeID);
            $resp = ['success' => true, 'data' => ['degreeID' => $degreeID]];
            return $handler->getResponse()->jsonResponse($resp, 200);
        } else {
            $resp = ['success' => false];
            return $handler->getResponse()->jsonResponse($resp, 401);
        }
    }


    /**
     * This method handles the adding and removing of courses from our active degree.
     * We make sure to check if the user has an active degree and informs the user
     * if they need to add one. We return HTTP status codes to handle a valid response.
     * @param Handler $handler
     * @return false|string
     */
    public function toggleCourseToDegree(Handler $handler): bool|string
    {
        $body = $handler->getRequest()->getBody();
        $user = $this->users->getUser(Session::get(SESSION_USERID));

        $degreeID = $user["activeDegreeID"];

        if (is_null($degreeID)) {
            $resp = ['success' => false, 'data' => ['Status' => 'No active degree']];
            return $handler->getResponse()->jsonResponse($resp, 500);
        }

        $courseID = $body["courseID"];

        $isInActiveDegree = $this->courses->checkIfCourseExistsInActiveDegree($courseID);

        if ($isInActiveDegree) {
            $this->courses->deleteDegreeCourse($degreeID, $courseID);
            $resp = ['success' => true, 'data' => ['Status' => 'Deleted']];
        } else {
            $this->courses->insertDegreeCourse($degreeID, $courseID);
            $resp = ['success' => false, 'data' => ['Status' => 'Inserted']];
        }

        return $handler->getResponse()->jsonResponse($resp, 200);
    }
}
