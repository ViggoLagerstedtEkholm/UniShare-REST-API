<?php

namespace App\controllers;

use App\Models\MVCModels\Projects;
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
        $this->setMiddlewares(new AuthenticationMiddleware(['view', 'uploadProject', 'deleteProject', 'getProject', 'updateProject']));

        $this->projects = new Projects();
        $this->imageHandler = new ImageHandler();
    }

    /**
     * This method shows the project add page.
     * @return string
     */
    public function add(): string
    {
        return $this->display('projects/add', 'projects', []);
    }

    /**
     * This method shows the project update page.
     * @return string
     */
    public function update(): string
    {
        if (isset($_GET["ID"])) {
            $ID = $_GET["ID"];

            $params = [
                "projectID" => $ID
            ];

            return $this->display('projects/update', 'projects', $params);
        } else {
            Application::$app->redirect("./");
        }
    }

    /**
     * This method handles adding new projects.
     * @param Request $request
     */
    public function uploadProject(Request $request)
    {
        $fileUploadName = 'project-file';
        $userID = Session::get(SESSION_USERID);
        $body = $request->getBody();

        if ($body["customCheck"] == "Off") {
            $params = [
                "link" => $body["link"],
                "name" => $body["name"],
                "description" => $body["description"],
                "project-file" => $fileUploadName,
                "customCheck" => $body["customCheck"]
            ];
        } else {
            $params = [
                "link" => $body["link"],
                "name" => $body["name"],
                "description" => $body["description"],
                "custom" => $body["custom"],
                "customCheck" => $body["customCheck"]
            ];
        }

        //Check all fields + image validity
        $errors = $this->projects->validate($params);

        if (count($errors) > 0) {
            $errorList = http_build_query(array('error' => $errors));
            Application::$app->redirect("/UniShare/profile?ID=$userID&$errorList");
            exit();
        }

        if ($params["customCheck"] == "On") {
            $image = $this->imageHandler->createImageFromText($params["custom"]);
        } else {
            $originalImage = $_FILES[$fileUploadName];
            $image = $this->imageHandler->handleUploadResizing($originalImage);
        }

        $this->projects->uploadProject($params, $userID, $image);
        Application::$app->redirect("/UniShare/profile?ID=$userID");
    }

    /**
     * This method handles deleting projects.
     * @param Request $request
     * @return false|string
     */
    public function deleteProject(Request $request): bool|string
    {
        $courseRequest = $request->getBody();

        $projectID = $courseRequest["projectID"];
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

    public function getProjectForEdit(Request $request)
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

    public function updateProject(Request $request)
    {
        $fileUploadName = 'project-file';
        $userID = Session::get(SESSION_USERID);

        $body = $request->getBody();
        $projectID = $body["projectID"];

        $params = [
            "projectID" => $projectID,
            "link" => $body["link"],
            "name" => $body["name"],
            "description" => $body["description"],
            "project-file" => $fileUploadName,
            "customCheck" => "Off"
        ];

        //Check all fields + image validity
        $errors = $this->projects->validate($params);

        if (count($errors) > 0) {
            $errorList = http_build_query(array('error' => $errors));
            Application::$app->redirect("/UniShare/project/update?ID= $projectID&$errorList");
            exit();
        }

        $canUpdate = $this->projects->checkIfUserOwner($userID, $projectID);

        if ($canUpdate) {
            $originalImage = $_FILES[$fileUploadName];
            $resizedImage = $this->imageHandler->handleUploadResizing($originalImage);
            $this->projects->updateProject($projectID, $params, $resizedImage);
            Application::$app->redirect("../profile?ID=$userID");
        } else {
            Application::$app->redirect("../");
        }
    }
}
