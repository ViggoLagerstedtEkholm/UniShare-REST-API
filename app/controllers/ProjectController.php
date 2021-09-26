<?php

namespace App\controllers;

use App\Core\Exceptions\GDResizeException;
use App\core\Exceptions\NotFoundException;
use App\includes\Validate;
use App\Models\Projects;
use App\Core\Request;
use App\Core\Session;
use App\Core\Application;
use App\Core\ImageHandler;
use App\Middleware\AuthenticationMiddleware;

/**
 * Project controller for handling projects.
 * @author Viggo Lagestedt Ekholm
 */
class ProjectController extends Controller
{
    private Projects $projects;
    private ImageHandler $imageHandler;

    function __construct()
    {
        $this->setMiddlewares(new AuthenticationMiddleware(['uploadProject', 'deleteProject', 'getProject', 'updateProject']));

        $this->projects = new Projects();
        $this->imageHandler = new ImageHandler();
    }

    /**
     * Get project from ID.
     * @param Request $request
     * @return bool|string
     */
    public function get(Request $request): bool|string
    {
        $body = $request->getBody();
        $ID = $body['profileID'];
        $projects = $this->projects->getProjects($ID);
        $newArrData = array();
        foreach($projects as $key => $value){
            $newArrData[$key] = $value;
            $newArrData[$key]['image'] = base64_encode($value['image']);
        }

        $resp = ['projects' => $newArrData];
        return $this->jsonResponse($resp, 200);
    }

    /**
     * This method handles adding new projects.
     * @param Request $request
     * @return bool|string|null
     * @throws GDResizeException
     */
    public function upload(Request $request): bool|string|null
    {
        $fileUploadName = 'file';
        $userID = Session::get(SESSION_USERID);
        $body = $request->getBody();

        $body['customCheck'] = json_decode($body['customCheck']);

        if ($body["customCheck"] == false) {
            $params = [
                "link" => $body["link"],
                "name" => $body["name"],
                "file" => $fileUploadName,
                "description" => $body["description"],
                "customCheck" => $body["customCheck"]
            ];
        } else {
            $params = [
                "link" => $body["link"],
                "name" => $body["name"],
                "description" => $body["description"],
                "text" => $body["text"],
                "customCheck" => $body["customCheck"]
            ];
        }


        $errors = $this->projects->validate($params);

        if (count($errors) > 0) {
            return $this->jsonResponse($errors, 500);
        }

        if ($params["customCheck"]) {
            $image = $this->imageHandler->createImageFromText($params["text"]);
        } else {
            $originalImage = $_FILES[$fileUploadName];
            $image = $this->imageHandler->handleUploadResizing($originalImage);
        }

        $this->projects->uploadProject($params, $userID, $image);
        return $this->jsonResponse($params, 200);
    }

    /**
     * This method handles deleting projects.
     * @param Request $request
     * @return false|string
     */
    public function delete(Request $request): bool|string
    {
        $body = $request->getBody();
        $projectID = $body["projectID"];

        $canRemove = $this->projects->checkIfUserOwner(Session::get(SESSION_USERID), $projectID);

        if ($canRemove) {
            $this->projects->deleteProject($projectID);
            $resp = ['success' => true, 'data' => ['Status' => true, 'ID' => $projectID]];
            return $this->jsonResponse($resp, 200);
        } else {
            $resp = ['success' => false, 'data' => ['Status' => false, 'ID' => $projectID]];
            return $this->jsonResponse($resp, 500);
        }
    }

    /**
     * This method gets project that we want to edit.
     * @param Request $request
     * @return false|string
     */
    public function edit(Request $request): bool|string
    {
        $body = $request->getBody();
        $projectID = $body["projectID"];

        if (empty($projectID)) {
            $resp = ['success' => false, 'status' => 'No matching ID!'];
            return $this->jsonResponse($resp, 404);
        }

        $project = $this->projects->getProject($projectID);

        //Check if the currently logged in user is the one that owns the project.
        if ($project["userID"] == Session::get(SESSION_USERID)) {
            $name = $project["name"];
            $link = $project["link"];
            $description = $project["description"];
            $resp = ['success' => true, 'data' => ['Name' => $name, 'Link' => $link, 'Description' => $description]];
            return $this->jsonResponse($resp, 200);
        } else {
            $resp = ['success' => false, 'data' => ['Project' => $project]];
            return $this->jsonResponse($resp, 403);
        }
    }

    /**
     * Update project.
     * @param Request $request
     * @return bool|string
     * @throws GDResizeException
     */
    public function update(Request $request): bool|string
    {
        $fileUploadName = 'file';
        $userID = Session::get(SESSION_USERID);

        $body = $request->getBody();
        $projectID = $body["projectID"];

        $params = [
            "projectID" => $projectID,
            "link" => $body["link"],
            "name" => $body["name"],
            "description" => $body["description"],
            "file" => $fileUploadName,
            "customCheck" => "Off"
        ];

        //Check all fields + image validity
        $errors = $this->projects->validate($params);

        if (count($errors) > 0) {
            $errorList = http_build_query(array('error' => $errors));
            return $this->jsonResponse($errorList, 500);
        }

        $canUpdate = $this->projects->checkIfUserOwner($userID, $projectID);

        if ($canUpdate) {
            $originalImage = $_FILES[$fileUploadName];
            $resizedImage = $this->imageHandler->handleUploadResizing($originalImage);
            $this->projects->updateProject($projectID, $params, $resizedImage);
            return $this->jsonResponse(true, 200);
        } else {
            return $this->jsonResponse(false, 401);
        }
    }
}
