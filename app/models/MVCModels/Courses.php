<?php
namespace App\Models\MVCModels;
use App\Models\Templates\Course;
use App\Core\Session;

class Courses extends Database
{
  function getCoursesCount(){
     $sql = "SELECT Count(*) FROM courses";
     $result = $this->executeQuery($sql);
     return $result->fetch_assoc()["Count(*)"];
  }

  function getCourseCountSearch($search){
    $sql = "SELECT Count(*) FROM courses WHERE name LIKE ?";
    $search = '%' . $search . '%';
    $result = $this->executeQuery($sql, 's', array($search));
    return $result->fetch_assoc()["Count(*)"];
  }

  function fetchCoursesSearch($from, $to, $option, $filterOrder, $search = null){
     $queryBuilder = [
       "select" => "SELECT courseID, name, credits, duration, added, fieldOfStudy, location FROM courses ",
       "condition" => "WHERE name LIKE ? ",
       "ordering" => "ORDER BY $option $filterOrder ",
       "LIMIT" => "LIMIT ?, ?;"
     ];

     if(is_null($search)){
       $sql = $queryBuilder["select"] . $queryBuilder["ordering"] . $queryBuilder["LIMIT"];
     }else{
       $sql = $queryBuilder["select"] . $queryBuilder["condition"] . $queryBuilder["ordering"] . $queryBuilder["LIMIT"];
     }

     if(is_null($search)){
       $result = $this->executeQuery($sql, 'ss', array($from, $to));
     }else{
       $search = '%' . $search . '%';
       $result = $this->executeQuery($sql, 'sss', array($search, $from, $to));
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
         $course->field_of_study = $row['fieldOfStudy'];
         $course->location = $row['location'];
         $course->existsInActiveDegree = $this->checkIfCourseExistsInActiveDegree($course->ID);
         $courses[] = $course;
     }
     return $courses;
   }

  function insertCourse(Course $course){
    $sql = "INSERT INTO courses (name, credits, duration, added, fieldOfStudy, location) values(?,?,?,?,?,?);";
    $result = $this->insertOrUpdate($sql, 'ssssss', array($course->name, $course->credits,  $course->duration, $course->added,
    $course->field_of_study, $course->location));
  }

  function deleteCourse(){
    //TODO
  }

  function getArthimetricMeanScore($courseID){
    $sql = "SELECT SUM(rating), COUNT(*) FROM rating WHERE courseID = ?;";
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

  function getCourse($ID){
    $sql = "SELECT * FROM courses WHERE courseID = ?;";
    $result = $this->executeQuery($sql, 'i', array($ID));
    $data = $result->fetch_array();

    $course = new Course();
    $course->name = $data["name"];
    $course->ID = $data["courseID"];
    $course->credits = $data["credits"];
    $course->duration = $data["duration"];
    $course->added = $data["added"];
    $course->field_of_study = $data["fieldOfStudy"];
    $course->location = $data["location"];
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
   $sql = "SELECT * FROM courses;";
   $result = $this->executeQuery($sql);

   while ($row = $result->fetch_assoc())
   {
        $course = new Course();
        $course->ID = $row["courseID"];
        $course->name = $row["name"];
        $course->credits = $row["credits"];
        $course->duration = $row["duration"];
        $course->fieldOfStudy = $row["fieldOfStudy"];
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
