<?php
namespace App\Controllers;

interface IDatabase {
  public function connect_database();
  public function close_database();
}
