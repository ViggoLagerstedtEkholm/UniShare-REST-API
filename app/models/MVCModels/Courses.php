<?php
namespace App\Models\MVCModels;
use App\Models\Templates\Course;

class Courses extends Database
{
  function getCoursesCount(){
     if($this->getConnection()->connect_error){
       die('Connection Failed: ' . $this->getConnection()->connect_error);
     }else{
       $changedate = "";
         $sql = "SELECT COUNT(*) FROM courses";
         $result = $this->getConnection()->query($sql);
         $count = $result->fetch_assoc()["COUNT(*)"];
         return $count;
     }
     return 0;
  }

  function getCourseCountSearch($search){
    $sql = "SELECT Count(*) FROM courses WHERE name LIKE ?";
    $stmt = mysqli_stmt_init($this->getConnection());
    mysqli_stmt_prepare($stmt, $sql);
    $search = '%' . $search . '%';
    mysqli_stmt_bind_param($stmt, "s", $search);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $count = $result->fetch_assoc()["Count(*)"];
    return $count;
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

     $stmt = mysqli_stmt_init($this->getConnection());
     mysqli_stmt_prepare($stmt, $sql);

     if(is_null($search)){
       mysqli_stmt_bind_param($stmt, "ss", $from, $to);
     }else{
       $search = '%' . $search . '%';
       mysqli_stmt_bind_param($stmt, "sss", $search, $from, $to);
     }

     mysqli_stmt_execute($stmt);
     $result = mysqli_stmt_get_result($stmt);

     $courses = array();
     while( $row = $result->fetch_array() )
     {
         $ID = $row['courseID'];
         $name = $row['name'];
         $credits = $row['credits'];
         $duration = $row['duration'];
         $added = $row['added'];
         $fieldOfStudy = $row['fieldOfStudy'];
         $location = $row['location'];

         $course = new Course();
         $course->ID = $ID;
         $course->name = $name;
         $course->credits = $credits;
         $course->duration = $duration;
         $course->added = $added;
         $course->field_of_study = $fieldOfStudy;
         $course->location = $location;
         $courses[] = $course;
     }

     mysqli_stmt_close($stmt);

     return $courses;
   }


  function uploadCourse(Course $course){

  }

  function deleteCourse(){

  }

  function getArthimetricMeanScore($courseID){
    $sql = "SELECT SUM(rating), COUNT(*) FROM rating WHERE courseID = ?;";
    $stmt = mysqli_stmt_init($this->getConnection());
    mysqli_stmt_prepare($stmt, $sql);
    mysqli_stmt_bind_param($stmt, "s", $courseID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    return $result->fetch_assoc();
  }

  function setRate($userID, $courseID, $rating){
    $sql = "INSERT INTO rating (userID, courseID, rating) values(?,?,?) ON DUPLICATE KEY UPDATE rating = ?;";
    $stmt = mysqli_stmt_init($this->getConnection());
    mysqli_stmt_prepare($stmt, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $userID, $courseID, $rating, $rating);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
  }

  function getRate($userID, $courseID){
    $sql = "SELECT rating FROM rating WHERE userID = ? AND courseID = ?";
    $stmt = mysqli_stmt_init($this->getConnection());
    mysqli_stmt_prepare($stmt, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $userID, $courseID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    return $result->fetch_assoc()["rating"];
  }

  function getCourse($ID){
    $sql = "SELECT * FROM courses WHERE courseID = ?;";
    $stmt = mysqli_stmt_init($this->getConnection());
    mysqli_stmt_prepare($stmt, $sql);
    mysqli_stmt_bind_param($stmt, "s", $ID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

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

  function getCoursesForDegree(){
    
  }

  function getCourses(){
   $courses = array();
   $sql = "SELECT * FROM courses;";
   $stmt = $this->getConnection()->prepare($sql);
   $stmt->execute();
   $result = $stmt->get_result(); // get the mysqli result

   while ($row = $result->fetch_assoc())
   {
        $ID = $row["courseID"];
        $name = $row["name"];
        $credits = $row["credits"];
        $duration = $row["duration"];
        $fieldOfStudy = $row["fieldOfStudy"];

        $course = new Course();
        $course->ID = $ID;
        $course->name = $name;
        $course->credits = $credits;
        $course->duration = $duration;
        $course->fieldOfStudy = $fieldOfStudy;
        $courses[] = $course;
    }

    return $courses;
  }

  function getTopCourses($count){
    //$SQL = "SELECT TOP $count FROM courses WHERE "
  }
}
