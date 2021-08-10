<?php
namespace App\Models\MVCModels;

abstract class Database{
  private $conn;

  function __construct(){
    $this->connect();
  }

  function __destruct(){
    $this->conn->close();
  }

  protected function connect(){
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $this->conn = mysqli_connect(DATABASE_SERVER_NAME, DATABASE_USER_NAME, DATABASE_PASSWORD, DATABASE_NAME);
    if(!$this->conn){
        die("Connection failed: " . mysqli_connect_error());
    }
  }

  protected function getConnection(){
    return $this->conn;
  }
}
