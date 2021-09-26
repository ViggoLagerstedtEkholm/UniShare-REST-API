<?php

namespace App\controllers;

use App\models\Courses;
use App\models\filtering\Filter;
use App\models\Friends;
use App\models\Search;
use App\Core\Request;
use App\Core\Session;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

/**
 * Content controller for handling searching and filtering website content.
 * @author Viggo Lagestedt Ekholm
 */
class ContentController extends Controller
{
    private Search $search;
    private Courses $courses;
    private Friends $friends;

    public function __construct()
    {
        $this->search = new Search();
        $this->courses = new Courses();
        $this->friends = new Friends();
    }

    /**
     * Get filters from search.
     * @param Request $request
     * @return array
     */
    #[Pure] #[ArrayShape(['page' => "int", 'filterOption' => "mixed|null", 'filterOrder' => "mixed|null", 'results_per_page_count' => "int", 'search' => "mixed|null"])]
    private function getFilter(Request $request): array
    {
        $body = $request->getBody();
        $search = empty($body['search']) ? null : $body['search'];
        $filterOption = $body['filterOption'] ?? null;
        $filterOrder = $body['filterOrder'] ?? 'DESC';
        $page = (int)$body['page'] ?? 1;
        $results_per_page_count = (int)$body['results_per_page_count'] ?? 7;
        if($results_per_page_count < 1){
            $results_per_page_count = 7;
        }

        return [
            'page' => $page,
            'filterOption' => $filterOption,
            'filterOrder' => $filterOrder,
            'results_per_page_count' => $results_per_page_count,
            'search' => $search
        ];
    }

    /**
     * Use the parameters to calculate the amount of pages required to showcase
     * all the items. The method filters people.
     * @param Request $request
     * @return string
     */
    public function people(Request $request): string
    {
        $userInputFilter = $this->getFilter($request);

        $filter = new Filter;
        $filter->setPage($userInputFilter['page']);
        $filter->setFilterOption($userInputFilter['filterOption'] ?? "visits");
        $filter->setFilterOrder($userInputFilter['filterOrder']);
        $filter->setResultsPerPageCount($userInputFilter['results_per_page_count']);
        $filter->setSearch($userInputFilter['search']);

        //Get the results in the interval of the pagination.
        $results = $this->search->doSearchPeople($filter);

        $data = array();
        foreach($results['result'] as $key => $value){
            $data[$key] = $value;
            $data[$key]['userImage'] = base64_encode($value['userImage']);
            if(Session::isLoggedIn()){
                $data[$key]['isFriend'] = $this->friends->isAlreadyFriends($value['usersID']);
                $data[$key]['isSent'] = $this->friends->isRequestSender($value['usersID']);
                $data[$key]['isReceived'] = $this->friends->isRequestReceiver($value['usersID']);
            }
        }

        $pagination = $results['pagination'];
        $total = $results['total'];

        $params = [
            'users' => $data,
            'total' => $total,
            'number_of_pages' => $pagination->getNumberOfPages(),
            'results_per_page_count' => $pagination->getResultsPerPage(),
            'start_page_first_result' => $pagination->getStartPageFirstResult()
        ];

        return $this->jsonResponse($params, 200);
    }

    public function posts(Request $request): string
    {
        $userInputFilter = $this->getFilter($request);

        $forumID = $request->getBody()['ID'];

        $filter = new Filter;
        $filter->setPage($userInputFilter['page']);
        $filter->setFilterOption($userInputFilter['filterOption'] ?? "date");
        $filter->setFilterOrder($userInputFilter['filterOrder']);
        $filter->setResultsPerPageCount($userInputFilter['results_per_page_count']);
        $filter->setSearch($userInputFilter['search']);

        $results = $this->search->doSearchPosts($filter, $forumID);

        $data = array();
        foreach($results['result'] as $key => $value){
            $data[$key] = $value;
            $data[$key]['userImage'] = base64_encode($value['userImage']);
        }

        $pagination = $results['pagination'];
        $total = $results['total'];

        $params = [
            'posts' => $data,
            'total' => $total,
            'number_of_pages' => $pagination->getNumberOfPages(),
            'results_per_page_count' => $pagination->getResultsPerPage(),
            'start_page_first_result' => $pagination->getStartPageFirstResult()
        ];

        return $this->jsonResponse($params, 200);
    }

    /**
     * Use the parameters to calculate the amount of pages required to showcase
     * all the items. The method filters courses.
     * @param Request $request
     * @return bool|string|null
     */
    public function courses(Request $request): bool|string|null
    {
        $userInputFilter = $this->getFilter($request);

        $filter = new Filter;
        $filter->setPage($userInputFilter['page']);
        $filter->setFilterOption($userInputFilter['filterOption'] ?? "average_rating");
        $filter->setFilterOrder($userInputFilter['filterOrder']);
        $filter->setResultsPerPageCount($userInputFilter['results_per_page_count']);
        $filter->setSearch($userInputFilter['search']);

        //Get the results in the interval of the pagination.
        $results = $this->search->doSearchCoursesWithRatings($filter);

        $result = array();
        foreach($results['result'] as $key => $value){
            $temp = $value;
            if(Session::isLoggedIn()){
                $temp['isInActiveDegree'] = $this->courses->checkIfCourseExistsInActiveDegree($value['courseID']);
            }
            $result[$key] = $temp;
        }

        $pagination = $results['pagination'];
        $total = $results['total'];

        $params = [
            'courses' => $result,
            'total' => $total,
            'number_of_pages' => $pagination->getNumberOfPages(),
            'results_per_page_count' => $pagination->getResultsPerPage(),
            'start_page_first_result' => $pagination->getStartPageFirstResult()
        ];

        return $this->jsonResponse($params, 200);
    }

    /**
     * Use the parameters to calculate the amount of pages required to showcase
     * all the items. The method filters forums.
     * @param Request $request
     * @return string
     */
    public function forum(Request $request): string
    {
        $userInputFilter = $this->getFilter($request);

        $filter = new Filter;
        $filter->setPage($userInputFilter['page']);
        $filter->setFilterOption($userInputFilter['filterOption'] ?? "views");
        $filter->setFilterOrder($userInputFilter['filterOrder']);
        $filter->setResultsPerPageCount($userInputFilter['results_per_page_count']);
        $filter->setSearch($userInputFilter['search']);

        $results = $this->search->doSearchForums($filter);

        $pagination = $results['pagination'];
        $result = $results['result'];
        $total = $results['total'];

        $params = [
            'forums' => $result,
            'total' => $total,
            'number_of_pages' => $pagination->getNumberOfPages(),
            'results_per_page_count' => $pagination->getResultsPerPage(),
            'start_page_first_result' => $pagination->getStartPageFirstResult()
        ];

        return $this->jsonResponse($params, 200);
    }

    public function requests(Request $request): string
    {
        $userInputFilter = $this->getFilter($request);

        $filter = new Filter;
        $filter->setPage($userInputFilter['page']);
        $filter->setFilterOption($userInputFilter['filterOption'] ?? "date");
        $filter->setFilterOrder($userInputFilter['filterOrder']);
        $filter->setResultsPerPageCount($userInputFilter['results_per_page_count']);
        $filter->setSearch($userInputFilter['search']);

        $results = $this->search->doSearchRequests($filter);

        $pagination = $results['pagination'];
        $result = $results['result'];
        $total = $results['total'];

        $params = [
            'requests' => $result,
            'total' => $total,
            'number_of_pages' => $pagination->getNumberOfPages(),
            'results_per_page_count' => $pagination->getResultsPerPage(),
            'start_page_first_result' => $pagination->getStartPageFirstResult()
        ];

        return $this->jsonResponse($params, 200);
    }


    /**
     * Use the parameters to calculate the amount of pages required to showcase
     * all the items. The method filters forums.
     * @param Request $request
     * @return string
     */
    public function reviews(Request $request): string
    {
        $userInputFilter = $this->getFilter($request);

        $courseID = $request->getBody()['ID'];

        $filter = new Filter;
        $filter->setPage($userInputFilter['page']);
        $filter->setFilterOption($userInputFilter['filterOption'] ?? "helpful");
        $filter->setFilterOrder($userInputFilter['filterOrder']);
        $filter->setResultsPerPageCount($userInputFilter['results_per_page_count']);
        $filter->setSearch($userInputFilter['search']);

        $results = $this->search->doSearchReviews($filter, $courseID);

        $data = array();
        foreach($results['result'] as $key => $value){
            $data[$key] = $value;
           $data[$key]['userImage'] = base64_encode($value['userImage']);
        }

        $pagination = $results['pagination'];
        $total = $results['total'];

        $params = [
            'reviews' => $data,
            'total' => $total,
            'number_of_pages' => $pagination->getNumberOfPages(),
            'results_per_page_count' => $pagination->getResultsPerPage(),
            'start_page_first_result' => $pagination->getStartPageFirstResult()
        ];

        return $this->jsonResponse($params, 200);
    }

    /**
     * Use the parameters to calculate the amount of pages required to showcase
     * all the items. The method filters forums.
     * @param Request $request
     * @return string
     */
    public function comments(Request $request): string
    {
        $userInputFilter = $this->getFilter($request);

        $profileID = $request->getBody()['ID'];

        $filter = new Filter;
        $filter->setPage($userInputFilter['page']);
        $filter->setFilterOption($userInputFilter['filterOption'] ?? "date");
        $filter->setFilterOrder($userInputFilter['filterOrder']);
        $filter->setResultsPerPageCount($userInputFilter['results_per_page_count']);
        $filter->setSearch($userInputFilter['search']);

        //Get the results in the interval of the pagination.
        $results = $this->search->doSearchComments($filter, $profileID);

        $data = array();
        foreach($results['result'] as $key => $value){
            $data[$key] = $value;
            $data[$key]['userImage'] = base64_encode($value['userImage']);
        }

        $pagination = $results['pagination'];
        $total = $results['total'];

        $params = [
            'comments' => $data,
            'total' => $total,
            'number_of_pages' => $pagination->getNumberOfPages(),
            'results_per_page_count' => $pagination->getResultsPerPage(),
            'start_page_first_result' => $pagination->getStartPageFirstResult()
        ];

        return $this->jsonResponse($params, 200);
    }
}
