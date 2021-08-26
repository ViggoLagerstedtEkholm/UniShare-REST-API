<?php

namespace App\models\MVCModels;

use App\Includes\Validate;
use JetBrains\PhpStorm\Pure;

/**
 * Model for handling post queries.
 * @author Viggo Lagestedt Ekholm
 */
class Projects extends Database implements IValidate
{
    /**
     * Check if the user input is sufficient enough.
     * @param array $params
     * @return array
     */
    #[Pure] public function validate(array $params): array
    {
        $errors = array();

        if (Validate::arrayHasEmptyValue($params) === true) {
            $errors[] = EMPTY_FIELDS;
        }

        if (Validate::hasInvalidProjectLink($params["link"]) === true) {
            $errors[] = INVALID_PROJECT_LINK;
        }

        if (!Validate::hasValidUpload($params['project-file']) && $params['customCheck'] == "Off") {
            $errors[] = INVALID_IMAGE;
        }

        return $errors;
    }

    /**
     * Check if the user is owner for a give project ID.
     * @param int $userID
     * @param int $projectID
     * @return bool
     */
    function checkIfUserOwner(int $userID, int $projectID): bool
    {
        $sql = "SELECT projectID FROM projects WHERE userID = ?;";
        $result = $this->executeQuery($sql, 'i', array($userID));

        while ($row = $result->fetch_array()) {
            if ($row["projectID"] == $projectID) {
                return true;
            }
        }
        return false;
    }

    /**
     * Delete a given project with the ID.
     * @param int $projectID
     */
    function DeleteProject(int $projectID)
    {
        $sql = "DELETE FROM projects WHERE projectID = ?;";
        $this->delete($sql, 'i', array($projectID));
    }

    /**
     * Get all user projects.
     * @param int $ID
     * @return array|null
     */
    function getProjects(int $ID): array|null
    {
        $sql = "SELECT * FROM projects WHERE userID=?;";
        $result = $this->executeQuery($sql, 'i', array($ID));
        return $this->fetchResults($result);
    }

    /**
     * Get a given project with the ID.
     * @param int $ID
     * @return array|null
     */
    function getProject(int $ID): array|null
    {
        $sql = "SELECT * FROM projects WHERE projectID = ?;";
        $result = $this->executeQuery($sql, 'i', array($ID));
        return $result->fetch_assoc();
    }

    /**
     * Update project with new parameters.
     * @param int $ID
     * @param array $params
     * @param mixed $image
     */
    function updateProject(int $ID, array $params, mixed $image)
    {
        $sql = "UPDATE projects
            SET name = ?, description = ?, link = ?, image = ?
            WHERE projectID = ?;";
        $this->insertOrUpdate($sql, 'ssssi', array($params["name"], $params["description"], $params["link"], $image, $ID));
    }

    /**
     * Upload project with the given parameters.
     * @param int $ID
     * @param array $params
     * @param mixed $image
     */
    function uploadProject(array $params, int $ID, mixed $image)
    {
        $sql = "INSERT INTO projects (name, description, link, userID, image, added) values (?,?,?,?,?,?);";
        date_default_timezone_set("Europe/Stockholm");
        $date = date('Y-m-d H:i:s');

        $this->insertOrUpdate($sql, 'ssssss', array($params["name"], $params["description"], $params["link"], $ID, $image, $date));
    }
}
