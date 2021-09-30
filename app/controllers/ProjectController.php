<?php

namespace App\controllers;

use App\Core\Exceptions\GDResizeException;
use App\core\Handler;
use App\Core\ImageHandler;
use App\Core\Session;
use App\Middleware\AuthenticationMiddleware;
use App\Models\Projects;

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
     * @param Handler $handler
     * @return bool|string
     */
    public function get(Handler $handler): bool|string
    {
        $body = $handler->getRequest()->getBody();
        $ID = $body['profileID'];
        $projects = $this->projects->getProjects($ID);
        $newArrData = array();
        foreach($projects as $key => $value){
            $newArrData[$key] = $value;
            $newArrData[$key]['image'] = base64_encode($value['image']);
        }

        $resp = ['projects' => $newArrData];
        return $handler->getResponse()->jsonResponse($resp, 200);
    }

    /**
     * This method handles adding new projects.
     * @param Handler $handler
     * @return bool|string|null
     * @throws GDResizeException
     */
    public function upload(Handler $handler): bool|string|null
    {
        $fileUploadName = 'file';
        $userID = Session::get(SESSION_USERID);
        $body = $handler->getRequest()->getBody();

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
            return $handler->getResponse()->jsonResponse($errors, 422);
        }

        if ($params["customCheck"]) {
            $image = $this->imageHandler->createImageFromText($params["text"]);
        } else {
            $originalImage = $_FILES[$fileUploadName];
            $image = $this->imageHandler->handleUploadResizing($originalImage);
        }

        $this->projects->uploadProject($params, $userID, $image);
        return $handler->getResponse()->jsonResponse($params, 200);
    }

    /**
     * This method handles deleting projects.
     * @param Handler $handler
     */
    public function delete(Handler $handler)
    {
        $body = $handler->getRequest()->getBody();
        $projectID = $body["projectID"];

        $canRemove = $this->projects->checkIfUserOwner(Session::get(SESSION_USERID), $projectID);

        if ($canRemove) {
            $this->projects->deleteProject($projectID);
            $handler->getResponse()->setStatusCode(200);
        } else {
            $handler->getResponse()->setStatusCode(500);
        }
    }

    /**
     * This method gets project that we want to edit.
     * @param Handler $handler
     * @return false|string
     */
    public function edit(Handler $handler): bool|string
    {
        $body = $handler->getRequest()->getBody();
        $projectID = $body["projectID"];

        if (empty($projectID)) {
            $resp = ['success' => false, 'status' => 'No matching ID!'];
            return $handler->getResponse()->jsonResponse($resp, 404);
        }

        $project = $this->projects->getProject($projectID);

        //Check if the currently logged in user is the one that owns the project.
        if ($project["userID"] == Session::get(SESSION_USERID)) {
            $name = $project["name"];
            $link = $project["link"];
            $description = $project["description"];
            $resp = ['success' => true, 'data' => ['Name' => $name, 'Link' => $link, 'Description' => $description]];
            return $handler->getResponse()->jsonResponse($resp, 200);
        } else {
            $resp = ['success' => false, 'data' => ['Project' => $project]];
            return $handler->getResponse()->jsonResponse($resp, 403);
        }
    }

    /**
     * Update project.
     * @param Handler $handler
     * @return bool|string
     * @throws GDResizeException
     */
    public function update(Handler $handler): bool|string
    {
        $fileUploadName = 'file';
        $userID = Session::get(SESSION_USERID);

        $body = $handler->getRequest()->getBody();
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
            return $handler->getResponse()->jsonResponse($errors, 500);
        }

        $canUpdate = $this->projects->checkIfUserOwner($userID, $projectID);

        if ($canUpdate) {
            $originalImage = $_FILES[$fileUploadName];
            $resizedImage = $this->imageHandler->handleUploadResizing($originalImage);
            $this->projects->updateProject($projectID, $params, $resizedImage);
            return $handler->getResponse()->jsonResponse(true, 200);
        } else {
            return $handler->getResponse()->jsonResponse(false, 401);
        }
    }
}
