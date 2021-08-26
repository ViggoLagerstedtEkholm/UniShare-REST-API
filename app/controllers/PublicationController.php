<?php

namespace App\controllers;

use App\Models\MVCModels\Projects;
use App\Core\Request;
use App\Core\Session;
use App\Core\Application;
use App\Includes\Validate;
use App\Core\ImageHandler;
use App\Middleware\AuthenticationMiddleware;

/**
 * Publication controller for handling publications.
 * @author Viggo Lagestedt Ekholm
 */
class PublicationController extends Controller
{

    function __construct()
    {
        $this->setMiddlewares(new AuthenticationMiddleware(['view']));
    }

    /**
     * This method shows the publications page.
     * @return string
     */
    public function view(): string
    {
        return $this->display('publications', 'publications', []);
    }
}
