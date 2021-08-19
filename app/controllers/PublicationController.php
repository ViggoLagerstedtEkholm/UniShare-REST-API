<?php
namespace App\Controllers;
use App\Models\MVCModels\Projects;
use App\Core\Request;
use App\Core\Session;
use App\Core\Application;
use App\Includes\Validate;
use App\Core\ImageHandler;
use App\Middleware\AuthenticationMiddleware;

class PublicationController extends Controller{
  private $projects;
  private $imageHandler;

  function __construct(){
    $this->setMiddlewares(new AuthenticationMiddleware(['view']));
    $this->projects = new Projects();
    $this->imageHandler = new ImageHandler();
  }

  public function view(){
      return $this->display('publications','publications', []);
  }
}
