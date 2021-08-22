<?php
namespace App\Controllers;
use App\Core\Request;
use App\Middleware\AuthenticationMiddleware;
use App\Models\MVCModels\Forums;

class ForumController extends Controller{
  private $forums;

  function __construct(){
    $this->forums = new Forums();
  }

  public function view(){
    $this->setMiddlewares(new AuthenticationMiddleware(['update', 'post', 'delete']));
    return $this->display('forum','forum', []);
  }

  public function update(Request $request){

  }

  public function post(Request $request){

  }

  public function delete(Request $request){

  }
}
