<?php

namespace App\controllers;

use App\Core\Exceptions\GDResizeException;
use App\core\Handler;
use App\Core\ImageHandler;
use App\Core\Session;
use App\Middleware\AuthenticationMiddleware;
use App\Models\Projects;
use App\validation\ImageValidator;
use JetBrains\PhpStorm\ArrayShape;

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
        $this->setMiddlewares(new AuthenticationMiddleware(['edit', 'uploadProject', 'deleteProject', 'getProject', 'updateProject']));

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

        if (empty($project)) {
            return $handler->getResponse()->setStatusCode(404);
        } else {
            return $handler->getResponse()->jsonResponse($project, 200);
        }
    }


    /**
     * Update project.
     * @param Handler $handler
     * @return bool|int
     * @throws GDResizeException
     */
    public function update(Handler $handler): bool|int
    {
        $fileUploadName = 'file';
        $userID = Session::get(SESSION_USERID);

        $body = $handler->getRequest()->getBody();
        $projectID = $body["projectID"];

        $result = $this->validateUpload($handler, $fileUploadName);
        $params = $result['params'];
        $errors = $result['errors'];

        if (count($errors) > 0) {
            return $handler->getResponse()->jsonResponse($errors, 422);
        }

        $canUpdate = $this->projects->checkIfUserOwner($userID, $projectID);

        if($canUpdate && !empty($projectID)){
            if ($params["customCheck"]) {
                $image = $this->imageHandler->createImageFromText($params["text"]);
            } else {
                $originalImage = $_FILES[$fileUploadName];
                $image = $this->imageHandler->handleUploadResizing($originalImage);
            }
            $this->projects->updateProject($projectID, $params, $image);

            //Success
            return $handler->getResponse()->setStatusCode(200);
        }
        //Not your project!
        return $handler->getResponse()->setStatusCode(403);
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
        $result = $this->validateUpload($handler, $fileUploadName);
        $params = $result['params'];
        $errors = $result['errors'];

        $userID = Session::get(SESSION_USERID);

        if ($params["customCheck"]) {
            $image = $this->imageHandler->createImageFromText($params["text"]);
        } else {
            $originalImage = $_FILES[$fileUploadName];
            $image = $this->imageHandler->handleUploadResizing($originalImage);
        }

        if (count($errors) > 0) {
            return $handler->getResponse()->jsonResponse($errors, 422);
        }

        $success = $this->projects->uploadProject($params, $userID, $image);

        if($success){
            return $handler->getResponse()->setStatusCode(200);
        }else{
            return $handler->getResponse()->setStatusCode(500);
        }
    }

    #[ArrayShape(['params' => "array", 'errors' => "array"])]
    private function validateUpload(Handler $handler, $fileUploadName): bool|array|string|null
    {
        $body = $handler->getRequest()->getBody();

        $body['customCheck'] = json_decode($body['customCheck']);

        if ($body["customCheck"] === false) {
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

        return [
            'params' => $params,
            'errors' => $errors
        ];
    }
}
