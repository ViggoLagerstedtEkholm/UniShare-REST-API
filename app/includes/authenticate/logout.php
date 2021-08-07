<?php
namespace App\Includes\Authenticate;

session_start();
session_unset();
session_destroy();

header("location: ../../views/startpage.php");
