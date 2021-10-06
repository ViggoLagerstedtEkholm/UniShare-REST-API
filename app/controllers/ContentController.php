<?php

namespace App\controllers;

use App\core\Handler;
use App\models\Courses;
use App\models\filtering\Filter;
use App\models\Filtering\Pagination;
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
     * @return Filter
     */
    #[ArrayShape(['page' => "int", 'filterOption' => "mixed|null", 'filterOrder' => "mixed|null", 'results_per_page_count' => "int", 'search' => "mixed|null"])]
    private function getFilter(Request $request): Filter
    {
        $body = $request->getBody();
        $search = empty($body['search']) ? null : $body['search'];
        $filterOption = $body['filterOption'] ?? null;
        $filterOrder = $body['filterOrder'] ?? 'DESC';
        $page = (int)$body['page'] ?? 1;
        $results_per_page_count = (int)$body['results_per_page_count'] ?? 7;
        if($results_per_page_count < 1 || $results_per_page_count > 10){
            $results_per_page_count = 7;
        }

        $filter = new Filter;
        $filter->setPage($page);
        $filter->setFilterOption($filterOption);
        $filter->setFilterOrder($filterOrder);
        $filter->setResultsPerPageCount($results_per_page_count);
        $filter->setSearch($search);

        return $filter;
    }

    #[Pure] #[ArrayShape(['result' => "array|null", 'total' => "mixed", 'number_of_pages' => "", 'results_per_page_count' => "", 'start_page_first_result' => ""])]
    private function returnFilteredResult(Pagination $pagination, int $count, ?array $data): array
    {
        return [
            'result' => $data,
            'total' => $count,
            'number_of_pages' => $pagination->getNumberOfPages(),
            'results_per_page_count' => $pagination->getResultsPerPage(),
            'start_page_first_result' => $pagination->getStartPageFirstResult()
        ];
    }

    /**
     * Use the parameters to calculate the amount of pages required to showcase
     * all the items. The method filters people.
     * @param Handler $handler
     * @return bool|string
     */

    public function people(Handler $handler): bool|string
    {
        $filter = $this->getFilter($handler->getRequest());
        $option = $filter->getFilterOption();
        $filter->setFilterOption($option?? "visits");

        //Get the results in the interval of the pagination.
        $results = $this->search->doSearchPeople($filter);

        $result = array();
        foreach($results['result'] as $key => $value){
            $result[$key] = $value;
            $result[$key]['userImage'] = base64_encode($value['userImage']);
            if(Session::isLoggedIn()){
                $result[$key]['isFriend'] = $this->friends->isAlreadyFriends($value['usersID']);
                $result[$key]['isSent'] = $this->friends->isRequestSender($value['usersID']);
                $result[$key]['isReceived'] = $this->friends->isRequestReceiver($value['usersID']);
            }
        }

        $pagination = $results['pagination'];
        $count = $results['total'];
        return $handler->getResponse()->jsonResponse($this->returnFilteredResult($pagination, $count, $result), 200);
    }

    public function posts(Handler $handler): string
    {
        $filter = $this->getFilter($handler->getRequest());
        $option = $filter->getFilterOption();
        $filter->setFilterOption($option?? "date");
        $forumID = $handler->getRequest()->getBody()['ID'];

        $results = $this->search->doSearchPosts($filter, $forumID);

        $result = array();
        foreach($results['result'] as $key => $value){
            $result[$key] = $value;
            $result[$key]['userImage'] = base64_encode($value['userImage']);
        }

        $pagination = $results['pagination'];
        $count = $results['total'];
        return $handler->getResponse()->jsonResponse($this->returnFilteredResult($pagination, $count, $result), 200);
    }

    /**
     * Use the parameters to calculate the amount of pages required to showcase
     * all the items. The method filters courses.
     * @param Handler $handler
     * @return bool|string|null
     */
    public function courses(Handler $handler): bool|string|null
    {
        $filter = $this->getFilter($handler->getRequest());
        $option = $filter->getFilterOption();
        $filter->setFilterOption($option?? "average_rating");

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
        $count = $results['total'];
        return $handler->getResponse()->jsonResponse($this->returnFilteredResult($pagination, $count, $result), 200);
    }

    public function profileTotalRatings(Handler $handler): string
    {
        $filter = $this->getFilter($handler->getRequest());
        $option = $filter->getFilterOption();
        $filter->setFilterOption($option?? "review.added");

        $profileID = $handler->getRequest()->getBody()['ID'];


        //Get the results in the interval of the pagination.
        $results = $this->search->doSearchProfileRatings($filter, $profileID);

        $result = $results['result'];
        $pagination = $results['pagination'];
        $count = $results['total'];
        return $handler->getResponse()->jsonResponse($this->returnFilteredResult($pagination, $count, $result), 200);
    }

    public function profileTotalReviews(Handler $handler): string
    {
        $filter = $this->getFilter($handler->getRequest());
        $option = $filter->getFilterOption();
        $filter->setFilterOption($option?? "added");

        $profileID = $handler->getRequest()->getBody()['ID'];

        //Get the results in the interval of the pagination.
        $results = $this->search->doSearchProfileReviews($filter, $profileID);

        $result = array();
        foreach($results['result'] as $key => $value){
            $result[$key] = $value;
            $result[$key]['course'] = $this->courses->getCourse($value['courseID'])[0];
        }

        $pagination = $results['pagination'];
        $count = $results['total'];
        return $handler->getResponse()->jsonResponse($this->returnFilteredResult($pagination, $count, $result), 200);
    }

    /**
     * Use the parameters to calculate the amount of pages required to showcase
     * all the items. The method filters forums.
     * @param Handler $handler
     * @return string
     */
    public function forum(Handler $handler): string
    {
        $filter = $this->getFilter($handler->getRequest());
        $option = $filter->getFilterOption();
        $filter->setFilterOption($option?? "views");

        $results = $this->search->doSearchForums($filter);

        $result = $results['result'];
        $pagination = $results['pagination'];
        $count = $results['total'];
        return $handler->getResponse()->jsonResponse($this->returnFilteredResult($pagination, $count, $result), 200);
    }

    public function requests(Handler $handler): string
    {
        $filter = $this->getFilter($handler->getRequest());
        $option = $filter->getFilterOption();
        $filter->setFilterOption($option?? "date");

        $results = $this->search->doSearchRequests($filter);

        $result = $results['result'];
        $pagination = $results['pagination'];
        $count = $results['total'];
        return $handler->getResponse()->jsonResponse($this->returnFilteredResult($pagination, $count, $result), 200);
    }


    /**
     * Use the parameters to calculate the amount of pages required to showcase
     * all the items. The method filters forums.
     * @param Handler $handler
     * @return string
     */
    public function reviews(Handler $handler): string
    {
        $filter = $this->getFilter($handler->getRequest());
        $option = $filter->getFilterOption();
        $filter->setFilterOption($option?? "helpful");

        $courseID = $handler->getRequest()->getBody()['ID'];

        $results = $this->search->doSearchReviews($filter, $courseID);

        $result = array();
        foreach($results['result'] as $key => $value){
            $result[$key] = $value;
            $result[$key]['userImage'] = base64_encode($value['userImage']);
        }

        $pagination = $results['pagination'];
        $count = $results['total'];
        return $handler->getResponse()->jsonResponse($this->returnFilteredResult($pagination, $count, $result), 200);
    }

    /**
     * Use the parameters to calculate the amount of pages required to showcase
     * all the items. The method filters forums.
     * @param Handler $handler
     * @return string
     */
    public function comments(Handler $handler): string
    {
        $filter = $this->getFilter($handler->getRequest());
        $option = $filter->getFilterOption();
        $filter->setFilterOption($option ?? "date");

        $profileID = $handler->getRequest()->getBody()['ID'];

        //Get the results in the interval of the pagination.
        $results = $this->search->doSearchComments($filter, $profileID);

        $result = array();
        foreach($results['result'] as $key => $value){
            $result[$key] = $value;
            $result[$key]['userImage'] = base64_encode($value['userImage']);
        }

        $pagination = $results['pagination'];
        $count = $results['total'];
        return $handler->getResponse()->jsonResponse($this->returnFilteredResult($pagination, $count, $result), 200);
    }
}
