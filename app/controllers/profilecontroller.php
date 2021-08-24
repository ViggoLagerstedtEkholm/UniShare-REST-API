<?php
namespace App\Controllers;
use App\Core\Application;
use App\Core\Request;
use App\Core\Session;
use App\Core\ImageHandler;
use App\Models\MVCModels\Users;
use App\Models\MVCModels\Projects;
use App\Models\MVCModels\Degrees;
use App\Models\MVCModels\Comments;
use App\Models\MVCModels\Courses;
use App\Includes\Validate;
use App\Models\Templates\Project;
use App\Middleware\AuthenticationMiddleware;

class ProfileController extends Controller{
  private $imageHandler;
  private $users;
  private $projects;
  private $degrees;
  private $comments;
  private $courses;

  public function __construct(){
    $this->setMiddlewares(new AuthenticationMiddleware(['uploadImage', 'uploadProject', 'deleteProject', 'pubishCourse', 'getDegrees', 'removeCourseFromDegree', 'addComment']));

    $this->imageHandler = new ImageHandler();
    $this->users = new Users();
    $this->projects = new Projects();
    $this->degrees = new Degrees();
    $this->comments = new Comments();
    $this->courses = new Courses();

  }

  public function view(Request $request)
  {
    if(isset($_GET["ID"])){
      $ID = $_GET["ID"];
      if(!empty($ID)){

        if(isset($_GET['page'])){
          $page = $_GET['page'];
        }else{
          $page = 1;
        }

        $comment_count = $this->comments->getCommentCount($ID);

        $offsets = $this->calculateOffsets($comment_count, $page, 10);
        $start_page_first_result = $offsets['start_page_first_result'];
        $results_per_page = $offsets['results_per_page'];
        $number_of_pages = $offsets['number_of_pages'];

        $comments = $this->comments->getComments($start_page_first_result, $results_per_page, $ID);

        $user = $this->users->getUser($ID);
        $image = base64_encode($user["userImage"]);
        $degrees = $this->degrees->getDegrees($ID);
        $updatedVisitCount = $this->users->addVisitor($ID, $user);
        $projects = $this->projects->getProjects($ID);

        if(Session::isLoggedIn()){
          $sessionID = Session::get(SESSION_USERID);

          if($ID == $sessionID){
            $date = $this->users->addVisitDate($sessionID);
          }
        }

        $params = [
          'image' => $image,
          'comments' => $comments,
          'degrees' => $degrees,
          'updatedVisitCount' => $updatedVisitCount,
          'projects' => $projects,
          'page' => $page,
          'results_per_page' => $results_per_page,
          'number_of_pages' => $number_of_pages,
          'start_page_first_result' => $start_page_first_result,
          'currentPageID' => $ID,
          'visitDate' => $user["lastOnline"],
          'first_name' => $user["userFirstName"],
          'last_name' => $user["userLastName"],
          'display_name' => $user["userDisplayName"],
          'privilege' => $user["privilege"],
          'description' => $user["description"],
          'joined' => $user["joined"]
        ];

        return $this->display('profile','profile', $params);
      }
    }
    Application::$app->redirect("./");
  }

  public function uploadImage(Request $request){
    $fileUploadName = 'file';
    $sessionID = Session::get(SESSION_USERID);

    $isValid = Validate::hasValidUpload($fileUploadName);

    if($isValid){
      $originalImage = $_FILES[$fileUploadName];
      $image_resize = $this->imageHandler->handleUploadResizing($originalImage);
      $this->users->uploadImage($image_resize, $sessionID);
      Application::$app->redirect("../../profile?ID=$sessionID");
    }else{
      Application::$app->redirect("../../profile?ID=$sessionID&error=" . INVALID_UPLOAD);
    }
  }

  public function deleteComment(Request $request){
    $body = $request->getBody();
    $commentID = $body['commentID'];

    $canRemove = $this->comments->checkIfUserAuthor(Session::get(SESSION_USERID), $commentID);

    if($canRemove){
      $this->comments->deleteComment($commentID);
      $resp = ['success'=>true,'data'=>['Status'=>true, 'ID'=>$commentID]];
      return $this->jsonResponse($resp, 200);
    }else{
      $resp = ['success'=>true,'data'=>['Status'=>false, 'ID'=>$commentID]];
      return $this->jsonResponse($resp, 500);
    }
  }

  public function addComment(Request $request){
    $body = $request->getBody();

    $params = [
      'pageID' => $body['pageID'],
      'text' => $body['text'],
    ];

    $errors = $this->comments->validate($params);

    if(count($errors) > 0){
      $errorList = http_build_query(array('error' => $errors));
      $pageID = $params['pageID'];

      $resp = ['success'=>false,'data'=>['Status'=>'Invalid comment']];
      return $this->jsonResponse($resp, 500);
    }

    $posterID = Session::get(SESSION_USERID);
    $text = $body['text'];
    $profileID = $body['pageID'];

    $succeeded = $this->comments->addComment($posterID, $profileID, $text);
    if($succeeded){
      $resp = ['success'=>true,'data'=>['Status'=>'Added comment']];
      return $this->jsonResponse($resp, 200);
    }else{
      $resp = ['success'=>false,'data'=>['Status'=>'Error']];
      return $this->jsonResponse($resp, 500);
    }
  }

  public function removeCourseFromDegree(Request $request){
    $courseRequest = $request->getBody();

    $courseID = $courseRequest["courseID"];
    $degreeID = $courseRequest["degreeID"];

    $succeeded = $this->degrees->checkIfUserOwner(Session::get(SESSION_USERID), $degreeID);

    if($succeeded){
      $this->degrees->deleteCourseFromDegree($degreeID, $courseID);
      $resp = ['success'=>true,'data'=>['Status'=>true, 'ID'=>$courseID, 'degreeID' => $degreeID]];
      return $this->jsonResponse($resp, 200);
    }else{
      $resp = ['success'=>false,'data'=>['Status'=>'Error']];
      return $this->jsonResponse($resp, 403);
    }
  }
}
