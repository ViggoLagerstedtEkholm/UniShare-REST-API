<?php
namespace App\Controllers;
use App\Core\Request;
use App\Middleware\AuthenticationMiddleware;
use App\Models\MVCModels\Forums;
use App\Models\MVCModels\Posts;
use App\Core\Session;
use App\Core\Application;

class PostController extends Controller{
  private $posts;

  function __construct(){
    $this->setMiddlewares(new AuthenticationMiddleware(['update', 'post', 'delete', 'addForum']));
    $this->posts = new Posts();
  }
  
  public function view(Request $request){
    $body = $request->getBody();
    $params = [
      'forumID' => $body['ID'],
    ];
    return $this->display('post/add','post', $params);
  }
  
  public function addPost(Request $request){
    $body = $request->getBody();
    
    $forumID = $body['forumID'];
    $text = $body['text'];
    $userID = Session::get(SESSION_USERID);
    
    $errors = $this->posts->validate($body);
    
    if(count($errors) > 0){
      $errorList = http_build_query(array('error' => $errors));
      Application::$app->redirect("/UniShare/post?ID=$forumID&$errorList");
      exit();
    }

    $inserted = $this->posts->addPost($userID, $forumID, $text);
    
    if($inserted){
      Application::$app->redirect("/UniShare/forum?ID=$forumID");
    }else{
      Application::$app->redirect("/UniShare/post?ID=$forumID&error=failed");
    }
  }

  public function updatePost(Request $request){
    $body = $request->getBody();
  }

  public function deletePost(Request $request){
    $body = $request->getBody();
  }
}
