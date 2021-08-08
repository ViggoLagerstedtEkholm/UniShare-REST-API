<?php
namespace App\Models\MVCModels;

class Database{
  private $serverName = 'localhost';
  private $dbUserName = 'root';
  private $dbPassword = '';
  private $dbName = '9.0';

  private $conn;

  function __construct(){
    $this->connect();
  }

  function __destruct(){
    $this->close();
  }

  protected function connect(){
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $this->conn = mysqli_connect($this->serverName, $this->dbUserName, $this->dbPassword, $this->dbName);
    if(!$this->conn){
        die("Connection failed: " . mysqli_connect_error());
    }
  }

  protected function close(){
    $this->conn->close();
  }

  protected function getConnection(){
    return $this->conn;
  }
}
