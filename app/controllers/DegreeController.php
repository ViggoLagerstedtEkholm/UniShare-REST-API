<?php

namespace App\controllers;
use App\Core\Session;
use App\Core\Request;
use App\models\Courses;
use App\Models\Degrees;
use App\Middleware\AuthenticationMiddleware;
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
        $this->setMiddlewares(new AuthenticationMiddleware(['view', 'uploadDegree', 'deleteDegree', 'updateDegree', 'getdegrees', 'toggleCourseToDegree', 'getDegreesSettings']));

        $this->degrees = new Degrees();
        $this->users = new Users();
        $this->courses = new Courses();
    }

    /**
     * This method handles uploading a degree to a user.
     * @param Request $request
     * @return bool|string|null
     */
    public function uploadDegree(Request $request): bool|string|null
    {
        $body = $request->getBody();

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
            $errorList = http_build_query(array('error' => $errors));
            return $this->jsonResponse($errorList, 500);
        }

        $this->degrees->uploadDegree($params, Session::get(SESSION_USERID));
        return $this->jsonResponse(true, 200);
    }

    /**
     * This method gets the degrees from the logged in user.
     * @param Request $request
     * @return false|string
     */
    public function getDegrees(Request $request): false|string
    {
        $body = $request->getBody();
        $ID = $body['profileID'];
        $degrees = $this->degrees->getDegrees($ID);
        $resp = ['degrees' => $degrees];
        return $this->jsonResponse($resp, 200);
    }

    public function getActiveDegreeID(): bool|string|null
    {
        $ID = Session::get(SESSION_USERID);
        $activeDegreeID = $this->degrees->getActiveDegreeID($ID);
        return $this->jsonResponse($activeDegreeID, 200);
    }

    public function getDegreesSettings(): bool|string|null
    {
        $ID = Session::get(SESSION_USERID);
        $degrees = $this->degrees->getDegrees($ID);

        $names = array();
        foreach($degrees as $degree){
            $names[] = ['name' => $degree->name, 'isActive' => $degree->isActiveDegree, 'degreeID' => $degree->ID];
        }
        return $this->jsonResponse($names, 200);
    }

    /**
     * This method gets a specific degree from ID from the logged in user.
     * @param Request $request
     * @return false|string
     */
    public function getDegree(Request $request): false|string
    {
        $body = $request->getBody();
        $degreeID = $body["degreeID"];

        if (!empty($degreeID)) {
            $degree = $this->degrees->getDegree($degreeID);
        } else {
            $resp = ['success' => false, 'status' => 'No matching ID!'];
            return $this->jsonResponse($resp, 404);
        }

        if (!is_null($degree)) {
            $resp = ['success' => true, 'data' => ['degree' => $degree[0]]];
            return $this->jsonResponse($resp, 200);
        } else {
            $resp = ['success' => false];
            return $this->jsonResponse($resp, 500);
        }
    }

    /**
     * This method updates a specific degree from ID from the logged in user.
     * @param Request $request
     * @return bool|string|null
     */
    public function updateDegree(Request $request): bool|string|null
    {
        $body = $request->getBody();
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
            $errorList = http_build_query(array('error' => $errors));
            return $this->jsonResponse($errorList, 500);
        }

        $canUpdate = $this->degrees->checkIfUserOwner($userID, $degreeID);

        if ($canUpdate) {
            $this->degrees->updateDegree($body, $userID);
            return $this->setStatusCode(200);
        } else {
            return $this->setStatusCode(401);
        }
    }

    /**
     * This method deletes a specific degree from ID from the logged in user.
     * @param Request $request
     * @return false|string
     */
    public function deleteDegree(Request $request): bool|string
    {
        $body = $request->getBody();
        $degreeID = $body['degreeID'];
        $userID = Session::get(SESSION_USERID);

        $canDelete = $this->degrees->checkIfUserOwner($userID, $degreeID);

        if ($canDelete) {
            $this->degrees->deleteDegree($degreeID);
            $resp = ['success' => true, 'data' => ['degreeID' => $degreeID]];
            return $this->jsonResponse($resp, 200);
        } else {
            $resp = ['success' => false];
            return $this->jsonResponse($resp, 401);
        }
    }


    /**
     * This method handles the adding and removing of courses from our active degree.
     * We make sure to check if the user has an active degree and informs the user
     * if they need to add one. We return HTTP status codes to handle a valid response.
     * @param Request $request
     * @return false|string
     */
    public function toggleCourseToDegree(Request $request): bool|string
    {
        $body = $request->getBody();
        $user = $this->users->getUser(Session::get(SESSION_USERID));

        $degreeID = $user["activeDegreeID"];

        if (is_null($degreeID)) {
            $resp = ['success' => false, 'data' => ['Status' => 'No active degree']];
            return $this->jsonResponse($resp, 500);
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

        return $this->jsonResponse($resp, 200);
    }
}
