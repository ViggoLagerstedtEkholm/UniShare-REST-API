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

  protected function executeQuery($sql, $types = null, $params = null)
  {
      $stmt = $this->getConnection()->prepare($sql);
      if($params && $types){
        $stmt->bind_param($types, ...$params);
      }
      if(!$stmt->execute()) return false;
      $result = $stmt->get_result();
      $stmt->close();
      return $result;
  }

  protected function insertOrUpdate($sql, $types = null, $params = null){
    $stmt = $this->getConnection()->prepare($sql);
    $stmt->bind_param($types, ...$params);
    if(!$stmt->execute()){
      return false;
    }else{
      return true;
    }
  }

  protected function delete($sql, $types = null, $params = null, $deleteAll = false){
    $stmt = $this->getConnection()->prepare($sql);
    if(!$deleteAll && $types && $params){
      $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
  }

  protected function getConnection(){
    return $this->conn;
  }
}
