<?php
namespace App\Models\MVCModels;
use App\Models\Templates\Course;

class Courses extends Database
{
  function uploadCourse(Course $course){

  }

  function deleteCourse(){

  }

  function getCourse($ID){

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
