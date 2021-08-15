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

    $this->conn = new \MySQLi(DATABASE_SERVER_NAME, DATABASE_USER_NAME, DATABASE_PASSWORD, DATABASE_NAME);
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

  protected function builtMatchQuery($table, $search, $avoidID){
    $columns = $this->executeQuery("SELECT
                                   COLUMN_NAME
                                   FROM
                                   information_schema.COLUMNS
                                   WHERE TABLE_NAME = '$table'
                                   AND TABLE_SCHEMA = '9.0'");

     $searchTerms = array();
     while($column = $columns->fetch_assoc()){
       if($column['COLUMN_NAME'] != $avoidID){
         $searchTerms[] = $column['COLUMN_NAME'] . " LIKE '%$search%' ";
       }
     }

     $MATCH = implode(" OR ", $searchTerms);

    return $MATCH;
  }

  protected function getConnection(){
    return $this->conn;
  }
}
