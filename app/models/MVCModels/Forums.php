<?php

namespace App\Models\MVCModels;

use App\Core\Session;
use App\Includes\Validate;
use JetBrains\PhpStorm\Pure;
use Throwable;

/**
 * Model for handling forum queries.
 * @author Viggo Lagestedt Ekholm
 */
class Forums extends Database implements IValidate
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

        return $errors;
    }

    /**
     * Get specific forum.
     * @param int $forumID
     * @return array
     */
    function getForum(int $forumID): array
    {
        $sql = "SELECT * FROM forum WHERE forumID = ?;";
        $result = $this->executeQuery($sql, 'i', array($forumID));
        return $result->fetch_assoc();
    }

    /**
     * Insert forum transaction.
     * @param array $params
     * @return int|string|null
     * @throws Throwable
     */
    function insertForum(array $params): int|string|null
    {
        $userID = Session::get(SESSION_USERID);
        try {
            $this->getConnection()->begin_transaction();
            date_default_timezone_set("Europe/Stockholm");
            $date = date('Y-m-d H:i:s');

            $sql = "INSERT INTO forum (title, topic, creator, created) values(?,?,?,?);";
            $inserted = $this->insertOrUpdate($sql, 'ssis', array($params['title'], $params['topic'], $userID, $date));
            $forumID = $this->getConnection()->insert_id;

            if (!$inserted) {
                $this->getConnection()->rollback();
            }


            $sql = "INSERT INTO posts(userID, forumID, text, date) values(?,?,?,?);";
            $inserted = $this->insertOrUpdate($sql, 'iiss', array($userID, $forumID, $params["text"], $date));

            if (!$inserted) {
                $this->getConnection()->rollback();
            }

            $this->getConnection()->commit();
        } catch (Throwable $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        if ($inserted) {
            return $forumID;
        } else {
            return null;
        }
    }

    /**
     * Add view to forum.
     * @param int $forumID
     */
    function addViews(int $forumID)
    {
        $forum = $this->getForum($forumID);
        $views = $forum["views"];
        $updatedViews = $views + 1;
        $sql = "UPDATE forum SET views =? WHERE forumID = ?;";
        $this->insertOrUpdate($sql, 'ii', array($updatedViews, $forumID));
    }

    /**
     * Add view to forum.
     * @return array|null
     */
    function getTOP10Forums(): array|null
    {
        $sql = "SELECT *
            FROM forum
            ORDER BY views
            DESC
            LIMIT 10;";
        $result = $this->executeQuery($sql);
        return $this->fetchResults($result);
    }

    /**
     * Get the forum count.
     * @return int|null
     */
    function getForumCount(): ?int
    {
        $sql = "SELECT Count(*) FROM forum";
        $result = $this->executeQuery($sql);
        return $result->fetch_assoc()["Count(*)"];
    }

    /**
     * Get the forum count search.
     * @param string $search
     * @return int|null
     */
    function getForumCountSearch(string $search): ?int
    {
        $MATCH = $this->builtMatchQuery('forum', $search, 'forumID');
        $sql = "SELECT Count(*) FROM forum WHERE $MATCH";
        $result = $this->executeQuery($sql);
        return $result->fetch_assoc()["Count(*)"];
    }

    /**
     * Get the forum count search.
     * @param int $from
     * @param int $to
     * @param string|null $option
     * @param string|null $filterOrder
     * @param string|null $search
     * @return array|null
     */
    function fetchForumsSearch(int $from, int $to, ?string $option, ?string $filterOrder, string $search = null): array|null
    {
        $option ?? $option = "title";
        $filterOrder ?? $filterOrder = "DESC";

        if (!is_null($search)) {
            $MATCH = $this->builtMatchQuery('forum', $search, 'forumID');
            $searchQuery = "SELECT *
                       FROM forum
                       WHERE $MATCH
                       ORDER BY $option $filterOrder
                       LIMIT ?, ?;";

            $result = $this->executeQuery($searchQuery, 'ii', array($from, $to));
        } else {
            $searchQuery = "SELECT *
                      FROM forum
                      ORDER BY $option $filterOrder
                      LIMIT ?, ?;";

            $result = $this->executeQuery($searchQuery, 'ii', array($from, $to));
        }

        return $this->fetchResults($result);
    }
}
