<?php

namespace App\controllers;

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
}
