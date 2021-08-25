<?php
namespace App\Models\MVCModels;

/**
 * Abstract class for handling querying the database and opening/closing the connection.
 * @abstract
 * @author Viggo Lagestedt Ekholm
 */
abstract class Database{
  private $conn;

  function __construct(){
    $this->connect();
  }

  function __destruct(){
    $this->conn->close();
  }

  /**
   * Create a connection to the database.
   */
  protected function connect(){
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $this->conn = new \MySQLi(DATABASE_SERVER_NAME, DATABASE_USER_NAME, DATABASE_PASSWORD, DATABASE_NAME);
    if(!$this->conn){
        die("Connection failed: " . mysqli_connect_error());
    }
  }

  /**
   * Execute a query with the given parameters and return the result.
   * @param sql string query.
   * @param types string types used in prepared statement.
   * @param params array of parameters in the query.
   * @return result
   */
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

  /**
   * Execute a insert/update query with the given parameters and return the result.
   * @param sql string query.
   * @param types string types used in prepared statement.
   * @param params array of parameters in the query.
   * @return bool did it succeed?
   */
  protected function insertOrUpdate($sql, $types = null, $params = null){
    $stmt = $this->getConnection()->prepare($sql);
    $stmt->bind_param($types, ...$params);
    if(!$stmt->execute()){
      $stmt->close();
      return false;
    }else{
      $stmt->close();
      return true;
    }
  }

  /**
   * Deletes from the database.
   * @param sql string query.
   * @param types string types used in prepared statement.
   * @param params array of parameters in the query.
   * @param deleteAll bool delete all.
   */
  protected function delete($sql, $types = null, $params = null, $deleteAll = false){
    $stmt = $this->getConnection()->prepare($sql);
    if(!$deleteAll && $types && $params){
      $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $stmt->close();
  }

  /**
   * Used to create an array of %LIKE% parameters for each column in a table,
   * this can be used in the search so the user can enter any type of column
   * string information to get a result in their search.
   * @param table the table we we want to create a query for.
   * @param search string keyword to search for.
   * @param avoidID int ID column to avoid.
   * @return string string to use in the query.
   */
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

  /**
   * This method is used to fetch the rows from a result aquired from the execueQuery() method.
   * @param result the table we we want to create a query for.
   * @return array of rows from the result.
   */
  protected function fetchResults($result) : array{
    $rows = array();
    while ($row = $result->fetch_assoc()) {
     $rows[] = $row;
    }
    return $rows;
  }

  /**
   * Get the connection instance.
   * @return connection mysqli.
   */
  protected function getConnection(){
    return $this->conn;
  }
}
