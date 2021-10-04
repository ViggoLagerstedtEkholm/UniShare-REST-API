<?php


namespace App\models;

use App\models\filtering\Filter;
use App\models\Filtering\Pagination;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Model for handling registering users.
 * @author Viggo Lagestedt Ekholm
 */
class Search extends Database
{

    function getTableCount(string $table, ?string $types, ?array $params): ?int
    {
        $sql = "SELECT Count(*) FROM $table";
        $result = $this->executeQuery($sql, $types, $params);
        return $result->fetch_assoc()["Count(*)"];
    }

    function getTableCountMatch(string $table, string $MATCH, ?string $types, ?array $params): ?int
    {
        $sql = "SELECT Count(*) FROM $table WHERE $MATCH";
        $result = $this->executeQuery($sql, $types, $params);
        return $result->fetch_assoc()["Count(*)"];
    }

    //<editor-fold desc="Search forums">
    #[ArrayShape(['total' => "int|null", 'result' => "array", 'pagination' => "\App\models\Filtering\Pagination"])]
    public function doSearchForums(Filter $filter): array
    {
        $searchColumns = array(
            'title',
            'topic',
            'created'
        );

        $option = $filter->getFilterOption();
        $order = $filter->getFilterOrder();
        $search = $filter->getSearch();

        if (!is_null($search)) {
            $MATCH = $this->buildMultipleTableQuery(array('forum'), $searchColumns, $search);

            $searchQuery = "SELECT forum.*, count(posts.forumID) as TOTAL_POSTS
                            FROM forum 
                            LEFT JOIN posts 
                            ON posts.forumID = forum.forumID 
                            GROUP BY posts.forumID
                            WHERE $MATCH
                            ORDER BY $option $order
                            LIMIT ?, ?;";

            $count = $this->getTableCountMatch('forum', $MATCH, null, null);
        } else {
            $searchQuery = "SELECT forum.*, count(posts.forumID) as TOTAL_POSTS
                            FROM forum 
                            LEFT JOIN posts 
                            ON posts.forumID = forum.forumID 
                            GROUP BY posts.forumID
                            ORDER BY $option $order
                            LIMIT ?, ?;";
            $count = $this->getTableCount('forum', null, null);
        }

        $pagination = $this->calculateOffsets($count, $filter->getPage(), $filter->getResultsPerPageCount());
        $result = $this->executeQuery($searchQuery, 'ii', array($pagination->getStartPageFirstResult(), $pagination->getResultsPerPage()));

        return [
            'total' => $count,
            'result' => $this->fetchResults($result),
            'pagination' => $pagination
        ];
    }
    //</editor-fold>

    //<editor-fold desc="Search people">
    #[ArrayShape(['total' => "int", 'result' => "array", 'pagination' => "\App\models\Filtering\Pagination"])]
    public function doSearchPeople(Filter $filter): array
    {
        $searchColumns = array(
            'userFirstName',
            'userLastName',
            'userDisplayName',
            'lastOnline',
            'joined',
            'privilege'
        );

        $option = $filter->getFilterOption();
        $order = $filter->getFilterOrder();
        $search = $filter->getSearch();

        if (!is_null($search)) {
            $MATCH = $this->buildMultipleTableQuery(array('users'), $searchColumns, $search);

            $searchQuery = "SELECT usersID, userFirstName, userLastName, userDisplayName, userImage, visits, lastOnline, joined, isSuspended
                      FROM users
                      WHERE $MATCH
                      ORDER BY $option $order
                      LIMIT ?, ?;";

            $count = $this->getTableCountMatch('users', $MATCH, null, null);
        } else {
            $searchQuery = "SELECT usersID, userFirstName, userLastName, userDisplayName, userImage, visits, lastOnline, joined, isSuspended
                     FROM users
                     ORDER BY $option $order
                     LIMIT ?, ?;";
            $count = $this->getTableCount('users', null, null);
        }

        $pagination = $this->calculateOffsets($count, $filter->getPage(), $filter->getResultsPerPageCount());
        $result = $this->executeQuery($searchQuery, 'ii', array($pagination->getStartPageFirstResult(), $pagination->getResultsPerPage()));

        return [
            'total' => $count,
            'result' => $this->fetchResults($result),
            'pagination' => $pagination
        ];
    }
    //</editor-fold>

    //<editor-fold desc="Search reviews">
    private function getReviewSearchCount(string $MATCH, int $courseID)
    {
        $sql = "SELECT Count(*)
                FROM review
                JOIN users
                ON review.userID = users.usersID
                WHERE review.courseID = ? AND ( $MATCH );";

        $result = $this->executeQuery($sql, 'i', array($courseID));
        return $result->fetch_assoc()["Count(*)"];
    }

    private function getReviewCount(int $courseID)
    {
        $sql = "SELECT Count(*)
                FROM review
                JOIN users
                ON review.userID = users.usersID
                WHERE review.courseID = ?";

        $result = $this->executeQuery($sql, 'i', array($courseID));
        return $result->fetch_assoc()["Count(*)"];
    }

    #[ArrayShape(['total' => "int", 'result' => "array", 'pagination' => "\App\models\Filtering\Pagination"])]
    public function doSearchReviews(Filter $filter, ?int $courseID): array
    {
        $searchColumns = array(
            'text',
            'updated',
            'added',
            'userDisplayName'
        );

        $option = $filter->getFilterOption();
        $order = $filter->getFilterOrder();
        $search = $filter->getSearch();

        if (!is_null($search)) {
            $MATCH = $this->buildMultipleTableQuery(array('review', 'users'), $searchColumns, $search);

            $searchQuery = "SELECT userImage, userDisplayName, review.*
                            FROM review
                            JOIN users
                            ON review.userID = users.usersID
                            WHERE review.courseID = ? AND ( $MATCH )
                            ORDER BY $option $order
                            LIMIT ?, ?;";

            $count = $this->getReviewSearchCount($MATCH, $courseID);
        } else {
            $searchQuery = "SELECT userImage,users.userDisplayName, review.*
                            FROM review
                            JOIN users
                            ON review.userID = users.usersID
                            WHERE review.courseID = ?
                            ORDER BY $option $order
                            LIMIT ?, ?;";

            $count = $this->getReviewCount($courseID);
        }
        $pagination = $this->calculateOffsets($count, $filter->getPage(), $filter->getResultsPerPageCount());
        $result = $this->executeQuery($searchQuery, 'iii', array($courseID, $pagination->getStartPageFirstResult(), $pagination->getResultsPerPage()));

        return [
            'total' => $count,
            'result' => $this->fetchResults($result),
            'pagination' => $pagination
        ];
    }
    //</editor-fold>

    //<editor-fold desc="Search posts">
    private function getPostsSearchCount(string $MATCH, int $forumID)
    {
        $sql = "SELECT Count(*)
                FROM posts
                JOIN users
                ON posts.userID = users.usersID
                WHERE forumID = ? AND ( $MATCH );";

        $result = $this->executeQuery($sql, 'i', array($forumID));
        return $result->fetch_assoc()["Count(*)"];
    }

    private function getPostsCount($forumID)
    {
        $sql = "SELECT Count(*)
                FROM posts
                JOIN users
                ON posts.userID = users.usersID
                WHERE forumID = ?";

        $result = $this->executeQuery($sql, 'i', array($forumID));
        return $result->fetch_assoc()["Count(*)"];
    }

    #[ArrayShape(['total' => "int", 'result' => "array", 'pagination' => "\App\models\Filtering\Pagination"])]
    public function doSearchPosts(Filter $filter, ?int $forumID): array
    {
        $searchColumns = array(
            'text',
            'date',
            'userDisplayName'
        );

        $option = $filter->getFilterOption();
        $order = $filter->getFilterOrder();
        $search = $filter->getSearch();

        if (!is_null($search)) {
            $MATCH = $this->buildMultipleTableQuery(array('posts', 'users'), $searchColumns, $search);

            $searchQuery = "SELECT posts.*, users.userDisplayName, users.userImage 
                            FROM posts 
                            JOIN users 
                            ON posts.userID = users.usersID
                            WHERE forumID = ? AND ( $MATCH )
                            ORDER BY $option $order
                            LIMIT ?, ?;";

            $count = $this->getPostsSearchCount($MATCH, $forumID);
        } else {
            $searchQuery = "SELECT posts.*, users.userDisplayName, users.userImage 
                            FROM posts 
                            JOIN users 
                            ON posts.userID = users.usersID
                            WHERE forumID = ?
                            ORDER BY $option $order
                            LIMIT ?, ?;";

            $count = $this->getPostsCount($forumID);
        }

        $pagination = $this->calculateOffsets($count, $filter->getPage(), $filter->getResultsPerPageCount());
        $result = $this->executeQuery($searchQuery, 'iii', array($forumID, $pagination->getStartPageFirstResult(), $pagination->getResultsPerPage()));

        return [
            'total' => $count,
            'result' => $this->fetchResults($result),
            'pagination' => $pagination
        ];
    }
    //</editor-fold>

    //<editor-fold desc="Search courses">
    #[ArrayShape(['total' => "int", 'result' => "array", 'pagination' => "\App\models\Filtering\Pagination"])]
    public function doSearchCoursesWithRatings(Filter $filter): array
    {
        $ignoreColumns = array(
            'name',
            'credits',
            'added',
            'country',
            'city',
            'university',
            'code'
        );

        $option = $filter->getFilterOption();
        $order = $filter->getFilterOrder();
        $search = $filter->getSearch();

        if (!is_null($search)) {
            $MATCH = $this->buildMultipleTableQuery(array('courses'), $ignoreColumns, $search);
            $searchQuery = "SELECT AVG(rating) AS average_rating, courses.*
                      FROM rating
                      RIGHT JOIN courses
                      ON rating.courseID = courses.courseID
                      WHERE $MATCH
                      GROUP BY courses.courseID
                      ORDER BY $option $order
                      LIMIT ?, ?;";

            $count = $this->getTableCountMatch('courses', $MATCH, null, null);
        } else {
            $searchQuery = "SELECT AVG(rating) AS average_rating, courses.*
                      FROM rating
                      RIGHT JOIN courses
                      ON rating.courseID = courses.courseID
                      GROUP BY courses.courseID
                      ORDER BY $option $order
                      LIMIT ?, ?;";

            $count = $this->getTableCount('courses', null, null);
        }
        $pagination = $this->calculateOffsets($count, $filter->getPage(), $filter->getResultsPerPageCount());
        $result = $this->executeQuery($searchQuery, 'ii', array($pagination->getStartPageFirstResult(), $pagination->getResultsPerPage()));

        return [
            'total' => $count,
            'result' => $this->fetchResults($result),
            'pagination' => $pagination
        ];
    }
    //</editor-fold>

    //<editor-fold desc="Search comments">
    private function getCommentSearchCount(string $MATCH, int $profileID)
    {
        $sql = "SELECT Count(*)
            FROM profilecomment
            JOIN users
            ON author = usersID
            WHERE profile = ? AND ( $MATCH );";

        $result = $this->executeQuery($sql, 'i', array($profileID));
        return $result->fetch_assoc()["Count(*)"];
    }

    private function getCommentCount(int $profileID)
    {
        $sql = "SELECT Count(*)
            FROM profilecomment
            JOIN users
            ON author = usersID
            WHERE profile = ?";

        $result = $this->executeQuery($sql, 'i', array($profileID));
        return $result->fetch_assoc()["Count(*)"];
    }

    #[ArrayShape(['total' => "int", 'result' => "array", 'pagination' => "\App\models\Filtering\Pagination"])]
    public function doSearchComments(Filter $filter, ?int $profileID): array
    {
        $ignoreColumns = array(
            'text',
            'date',
            'userDisplayName',
        );

        $option = $filter->getFilterOption();
        $order = $filter->getFilterOrder();
        $search = $filter->getSearch();

        if (!is_null($search)) {
            $MATCH = $this->buildMultipleTableQuery(array('profilecomment', 'users'), $ignoreColumns, $search);

            $searchQuery = "SELECT profilecomment.*, userImage, userDisplayName
            FROM profilecomment
            JOIN users
            ON author = usersID
            WHERE profile = ? AND ( $MATCH )
            ORDER BY $option $order
            LIMIT ?, ?;";

            $count = $this->getCommentSearchCount($MATCH, $profileID);
        } else {
            $searchQuery = "SELECT profilecomment.*, userImage, userDisplayName
            FROM profilecomment
            JOIN users
            ON author = usersID
            WHERE profile = ?
            ORDER BY $option $order
            LIMIT ?, ?;";

            $count = $this->getCommentCount($profileID);
        }
        $pagination = $this->calculateOffsets($count, $filter->getPage(), $filter->getResultsPerPageCount());
        $result = $this->executeQuery($searchQuery, 'iii', array($profileID, $pagination->getStartPageFirstResult(), $pagination->getResultsPerPage()));

        return [
            'total' => $count,
            'result' => $this->fetchResults($result),
            'pagination' => $pagination
        ];
    }
    //</editor-fold>

    //<editor-fold desc="Search requests">
    #[ArrayShape(['total' => "int|mixed|null", 'result' => "array", 'pagination' => "\App\models\Filtering\Pagination"])]
    public function doSearchRequests(Filter $filter): array
    {
        $ignoreColumns = array(
            'requestID',
            'userID',
        );

        $option = $filter->getFilterOption();
        $order = $filter->getFilterOrder();
        $search = $filter->getSearch();

        if (!is_null($search)) {
            $MATCH = $this->buildMultipleTableQuery(array('request'), $ignoreColumns, $search);

            $searchQuery = "SELECT * 
                            FROM request 
                            WHERE $MATCH
                            ORDER BY $option $order
                            LIMIT ?, ?;";

            $count = $this->getTableCountMatch('request', $MATCH, null, null);
        } else {
            $searchQuery = "SELECT * 
                            FROM request
                            ORDER BY $option $order
                            LIMIT ?, ?;";

            $count = $this->getTableCount('request', null, null);
        }
        $pagination = $this->calculateOffsets($count, $filter->getPage(), $filter->getResultsPerPageCount());
        $result = $this->executeQuery($searchQuery, 'ii', array($pagination->getStartPageFirstResult(), $pagination->getResultsPerPage()));

        return [
            'total' => $count,
            'result' => $this->fetchResults($result),
            'pagination' => $pagination
        ];
    }
    //</editor-fold>

    //<editor-fold desc="Search ratings">
    private function getRatingsProfileCountSearch(string $MATCH, int $profileID)
    {
        $sql = "SELECT Count(*)
                FROM courses
                JOIN rating
                ON rating.courseID = courses.courseID
                JOIN users
                ON rating.userID = users.usersID
                WHERE users.usersID = ? AND ( $MATCH )";

        $result = $this->executeQuery($sql, 'i', array($profileID));
        return $result->fetch_assoc()["Count(*)"];
    }

    private function getRatingsProfileCount(int $profileID)
    {
        $sql = "SELECT Count(*)
                FROM courses
                JOIN rating
                ON rating.courseID = courses.courseID
                JOIN users
                ON rating.userID = users.usersID
                WHERE users.usersID = ?";

        $result = $this->executeQuery($sql, 'i', array($profileID));
        return $result->fetch_assoc()["Count(*)"];
    }

    #[ArrayShape(['total' => "int", 'result' => "array", 'pagination' => "\App\models\Filtering\Pagination"])]
    public function doSearchProfileRatings(Filter $filter, ?int $profileID): array
    {
        $searchColumns = array(
            'name',
            'credits',
            'university',
            'added',
            'code',
            'city',
        );

        $option = $filter->getFilterOption();
        $order = $filter->getFilterOrder();
        $search = $filter->getSearch();

        if (!is_null($search)) {
            $MATCH = $this->buildMultipleTableQuery(array('courses'), $searchColumns, $search);

            $searchQuery = "SELECT courses.name, courses.credits, courses.university, 
                            courses.added, courses.code, rating.rating, courses.courseID, courses.city
                            FROM courses
                            JOIN rating
                            ON rating.courseID = courses.courseID
                            WHERE rating.userID = ? AND ( $MATCH )
                            ORDER BY $option $order
                            LIMIT ?, ?;";

            $count = $this->getRatingsProfileCountSearch($MATCH, $profileID);
        } else {
            $searchQuery = "SELECT courses.name, courses.credits, courses.university, 
                            courses.added, courses.code, rating.rating, courses.courseID, courses.city
                            FROM courses
                            JOIN rating
                            ON rating.courseID = courses.courseID
                            WHERE rating.userID = ?
                            ORDER BY $option $order
                            LIMIT ?, ?;";


            $count = $this->getRatingsProfileCount($profileID);
        }
        $pagination = $this->calculateOffsets($count, $filter->getPage(), $filter->getResultsPerPageCount());
        $result = $this->executeQuery($searchQuery, 'iii', array($profileID, $pagination->getStartPageFirstResult(), $pagination->getResultsPerPage()));

        return [
            'total' => $count,
            'result' => $this->fetchResults($result),
            'pagination' => $pagination
        ];
    }
    //</editor-fold>

    //<editor-fold desc="Search review">
    private function getReviewProfileCountSearch(string $MATCH, int $profileID)
    {
        $sql = "SELECT Count(*)
                FROM review
                JOIN courses
                ON review.courseID = courses.courseID
                WHERE review.userID = ? AND ( $MATCH )";

        $result = $this->executeQuery($sql, 'i', array($profileID));
        return $result->fetch_assoc()["Count(*)"];
    }

    private function getReviewProfileCount(int $profileID)
    {
        $sql = "SELECT Count(*)
                FROM review
                JOIN courses
                ON review.courseID = courses.courseID
                WHERE userID = ?";

        $result = $this->executeQuery($sql, 'i', array($profileID));
        return $result->fetch_assoc()["Count(*)"];
    }

    #[ArrayShape(['total' => "int", 'result' => "array", 'pagination' => "\App\models\Filtering\Pagination"])]
    public function doSearchProfileReviews(Filter $filter, ?int $profileID): array
    {
        $searchColumns = array(
            'text',
            'update',
            'added',
            'updated',
            'userDisplayName',
            'name',
            'university'
        );

        $option = $filter->getFilterOption();
        $order = $filter->getFilterOrder();
        $search = $filter->getSearch();

        if (!is_null($search)) {
            $MATCH = $this->buildMultipleTableQuery(array('review', 'courses'), $searchColumns, $search);

            $searchQuery = "SELECT review.*, courses.courseID
                            FROM review
                            JOIN courses
                            ON review.courseID = courses.courseID
                            WHERE userID = ? AND ( $MATCH )
                            ORDER BY $option $order
                            LIMIT ?, ?;";

            $count = $this->getReviewProfileCountSearch($MATCH, $profileID);
        } else {
            $searchQuery = "SELECT review.*, courses.courseID
                            FROM review
                            JOIN courses
                            ON review.courseID = courses.courseID
                            WHERE userID = ?
                            ORDER BY $option $order
                            LIMIT ?, ?";

            $count = $this->getReviewProfileCount($profileID);
        }
        $pagination = $this->calculateOffsets($count, $filter->getPage(), $filter->getResultsPerPageCount());
        $result = $this->executeQuery($searchQuery, 'iii', array($profileID, $pagination->getStartPageFirstResult(), $pagination->getResultsPerPage()));

        return [
            'total' => $count,
            'result' => $this->fetchResults($result),
            'pagination' => $pagination
        ];
    }
    //</editor-fold>

    /**
     * Calculate the offset for the pagination.
     * @param int $count
     * @param int $page
     * @param int $results_per_page
     * @return Pagination all parameters to query the database with the offsets calculated.
     */
    protected function calculateOffsets(int $count, int $page, int $results_per_page): Pagination
    {
        $number_of_pages = ceil($count / $results_per_page);
        $start_page_first_result = ($page - 1) * $results_per_page;

        $pagination = new Pagination();
        $pagination->setStartPageFirstResult($start_page_first_result);
        $pagination->setNumberOfPages($number_of_pages);
        $pagination->setResultsPerPage($results_per_page);

        return $pagination;
    }
}

