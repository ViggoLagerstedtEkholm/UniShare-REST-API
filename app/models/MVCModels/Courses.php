<?php
namespace App\Models\MVCModels;
use App\Models\Templates\Course;
use App\Models\Templates\Review;
use App\Core\Session;

class Courses extends Database
{
  function getCoursesCount(){
     $sql = "SELECT Count(*) FROM courses";
     $result = $this->executeQuery($sql);
     return $result->fetch_assoc()["Count(*)"];
  }

  function getCourseCountSearch($search){
    $MATCH = $this->builtMatchQuery('courses', $search, 'courseID');
    $sql = "SELECT Count(*) FROM courses WHERE $MATCH";
    $result = $this->executeQuery($sql);
    return $result->fetch_assoc()["Count(*)"];
  }

  function fetchCoursesSearch($from, $to, $option, $filterOrder, $search = null){
    $option ?? $option = "name";
    $filterOrder ?? $filterOrder = "DESC";

    if(!is_null($search)){
       $MATCH = $this->builtMatchQuery('courses', $search, 'courseID');
       $searchQuery = "SELECT AVG(rating) AS average_rating, courses.*
                      FROM rating
                      RIGHT JOIN courses
                      ON rating.courseID = courses.courseID
                      WHERE $MATCH
                      GROUP BY courses.courseID
                      ORDER BY $option $filterOrder
                      LIMIT ?, ?;";

      $result = $this->executeQuery($searchQuery, 'ii', array($from, $to));
    }else{
      $searchQuery = "SELECT AVG(rating) AS average_rating, courses.*
                      FROM rating
                      RIGHT JOIN courses
                      ON rating.courseID = courses.courseID
                      GROUP BY courses.courseID
                      ORDER BY $option $filterOrder
                      LIMIT ?, ?;";

    $result = $this->executeQuery($searchQuery, 'ii', array($from, $to));
    }

     $courses = array();
     while( $row = $result->fetch_array())
     {
         $course = new Course();
         $course->ID = $row['courseID'];
         $course->name = $row['name'];
         $course->credits = $row['credits'];
         $course->duration = $row['duration'];
         $course->added = $row['added'];
         $course->country = $row['country'];
         $course->city = $row['city'];
         $course->university = $row['university'];
         $course->rating = $row['average_rating'] ?? "No ratings";
         if(Session::isLoggedIn()){
           $course->existsInActiveDegree = $this->checkIfCourseExistsInActiveDegree($course->ID);
         }
         $courses[] = $course;
     }
     return $courses;
   }

  function getTOP10Courses(){
    $sql = "SELECT AVG(rating) AS average_rating, courses.*
            FROM rating
            JOIN courses
            ON rating.courseID = courses.courseID
            GROUP BY courses.courseID
            ORDER BY average_rating DESC
            LIMIT 10;";

    $result = $this->executeQuery($sql);

    $courses = array();
    while ($row = $result->fetch_assoc())
    {
         $course = new Course();
         $course->ID = $row["courseID"];
         $course->name = $row["name"];
         $course->credits = $row["credits"];
         $course->duration = $row["duration"];
         $course->added = $row["added"];
         $course->country = $row["country"];
         $course->city = $row["city"];
         $course->university = $row["university"];
         $course->rating = $row['average_rating'];
         $courses[] = $course;
     }
     return $courses;
  }

  function getPopularityRank($courseID){
    $sql = "SELECT *
            FROM
            (SELECT courseID, COUNT(rating), ROW_NUMBER() OVER (ORDER BY COUNT(rating) DESC) AS POPULARITY_RANK
            FROM rating
            GROUP BY courseID
            ORDER BY COUNT(rating) DESC) AS POPULARITY
            WHERE POPULARITY.courseID = ?;";

  return $this->executeQuery($sql, "i", array($courseID));
  }

  function getOverallRankingRating($courseID){
    $sql = "SELECT *
            FROM
            (SELECT courseID, AVG(rating), ROW_NUMBER() OVER (ORDER BY AVG(rating) DESC) AS RATING_RANK
            FROM rating
            GROUP BY courseID
            ORDER BY AVG(rating) DESC) AS RANKINGS
            WHERE RANKINGS.courseID = ?;";

    return $this->executeQuery($sql, "i", array($courseID));
  }

  function insertCourse($course){
    $sql = "INSERT INTO courses (name, credits, duration, added, country, city, university) values(?,?,?,?,?,?,?);";
    date_default_timezone_set("Europe/Stockholm");
    $date = date("Y-m-d",time());
    $hasSucceeded = $this->insertOrUpdate($sql, 'ssssss', array($course->name, $course->credits, $course->duration, $date, $course->field_of_study, $course->location));
    return $hasSucceeded;
  }

  function deleteCourse(){
    //TODO
  }

  function getArthimetricMeanScore($courseID){
    $sql = "SELECT AVG(rating), COUNT(rating) FROM rating WHERE courseID = ?;";
    $result = $this->executeQuery($sql, 'i', array($courseID));
    return $result->fetch_assoc();
  }

  function setRate($userID, $courseID, $rating){
    $sql = "INSERT INTO rating (userID, courseID, rating) values(?,?,?) ON DUPLICATE KEY UPDATE rating = ?;";
    $result = $this->insertOrUpdate($sql, 'iiii', array($userID, $courseID, $rating, $rating));
  }

  function getRate($userID, $courseID){
    $sql = "SELECT rating FROM rating WHERE userID = ? AND courseID = ?";
    $result = $this->executeQuery($sql, 'ii', array($userID, $courseID));
    $rating = $result->fetch_assoc()["rating"] ?? " - No rating set!";
    return $rating;
  }

  function insertReview($review){
    $sql = "INSERT INTO review (userID, courseID, text, fulfilling, environment, difficulty, grading, litterature, overall)
    values(?,?,?,?,?,?,?,?,?)
    ON DUPLICATE KEY UPDATE text = ?, fulfilling = ?, environment = ?, difficulty = ?, grading = ?, litterature = ?, overall = ?;";

    $userID = Session::get(SESSION_USERID);
    $success = $this->insertOrUpdate($sql, 'iisiiiiiisiiiiii', array($userID, $review->courseID, $review->text, $review->fulfilling, $review->environment, $review->difficulty, $review->grading, $review->litterature, $review->overall,
    $review->text, $review->fulfilling, $review->environment, $review->difficulty, $review->grading, $review->litterature, $review->overall));
    return $success;
  }

  function getReviews($courseID){
    $sql = "SELECT review.*, userDisplayName, userImage
            FROM review
            JOIN users
            ON review.userID = users.usersID
            WHERE courseID = ?";
    $result = $this->executeQuery($sql, 'i', array($courseID));

    $reviews = array();
    while ($row = $result->fetch_assoc())
    {
         $review = new Review();
         $review->userDisplayName = $row["userDisplayName"];
         $review->userImage = base64_encode($row["userImage"]);
         $review->userID = $row["userID"];
         $review->courseID = $row["courseID"];
         $review->text = $row["text"];
         $review->fulfilling = $row["fulfilling"];
         $review->environment = $row['environment'];
         $review->difficulty = $row['difficulty'];
         $review->grading = $row['grading'];
         $review->litterature	 = $row['litterature'];
         $review->overall	 = $row['overall'];
         $reviews[] = $review;
     }
     return $reviews;
  }

  function getCourse($ID){
    $sql = "SELECT * FROM courses WHERE courseID = ?;";
    $result = $this->executeQuery($sql, 'i', array($ID));
    $data = $result->fetch_array();

    $course = new Course();
    $course->ID = $data['courseID'];
    $course->name = $data['name'];
    $course->credits = $data['credits'];
    $course->duration = $data['duration'];
    $course->added = $data['added'];
    $course->country = $data['country'];
    $course->city = $data['city'];
    $course->university = $data['university'];
    return $course;
  }

  function insertDegreeCourse($degreeID, $courseID){
    $sql = "INSERT INTO degrees_courses (degreeID, courseID) values(?, ?);";
    $this->insertOrUpdate($sql, 'ii', array($degreeID, $courseID));
  }

  function deleteDegreeCourse($degreeID, $courseID){
    $sql = "DELETE FROM degrees_courses WHERE courseID = ? AND degreeID = ?;";
    $this->insertOrUpdate($sql, 'ii', array($courseID, $degreeID));
  }

  function getCourses(){
   $courses = array();
   $sql = "SELECT AVG(rating) AS average_rating, courses.*
           FROM rating
           JOIN courses
           ON rating.courseID = courses.courseID
           GROUP BY courses.courseID;";

   $result = $this->executeQuery($sql);

   while ($row = $result->fetch_assoc())
   {
        $course = new Course();
        $course->ID = $row["courseID"];
        $course->name = $row["name"];
        $course->credits = $row["credits"];
        $course->duration = $row["duration"];
        $course->fieldOfStudy = $row["fieldOfStudy"];
        $course->rating = $row['average_rating'];
        $courses[] = $course;
    }

    return $courses;
  }

  function checkIfCourseExistsInActiveDegree($courseID){
    $sql = "SELECT COUNT(*)
            FROM users
            JOIN degrees
            ON users.activeDegreeID = degrees.degreeID
            JOIN degrees_courses
            ON degrees_courses.degreeID = degrees.degreeID
            JOIN courses
            ON degrees_courses.courseID = courses.courseID
            WHERE courses.courseID = ? AND usersID = ?";

    $currentUser = Session::get(SESSION_USERID);
    $result = $this->executeQuery($sql, 'ii', array($courseID, $currentUser));
    $count = $result->fetch_assoc()["COUNT(*)"] ?? null;
    if($count > 0){
      return true;
    }else{
      return false;
    }
  }
}
