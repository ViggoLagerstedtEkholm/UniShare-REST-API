<?php

namespace App\models\MVCModels;

use MySQLi;
use mysqli_result;

/**
 * Abstract class for handling querying the database and opening/closing the connection.
 * @abstract
 * @author Viggo Lagestedt Ekholm
 */
abstract class Database
{
    private MySQLi $conn;

    function __construct()
    {
        $this->connect();
    }

    function __destruct()
    {
        $this->conn->close();
    }

    /**
     * Create a connection to the database.
     */
    protected function connect()
    {
        $this->conn = new MySQLi(DATABASE_SERVER_NAME, DATABASE_USER_NAME, DATABASE_PASSWORD, DATABASE_NAME);
        if (!$this->conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
    }

    /**
     * Execute a query with the given parameters and return the result.
     * @param string $sql
     * @param string|null $types
     * @param array|null $params
     * @return false|mysqli_result
     */
    protected function executeQuery(string $sql, string $types = null, array $params = null): false|mysqli_result
    {
        $stmt = $this->getConnection()->prepare($sql);
        if ($params && $types) {
            $stmt->bind_param($types, ...$params);
        }
        if (!$stmt->execute()) return false;
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    /**
     * Execute a insert/update query with the given parameters and return the result.
     * @param string $sql
     * @param string|null $types
     * @param array|null $params
     * @return bool did it succeed?
     */
    protected function insertOrUpdate(string $sql, string $types = null, array $params = null): bool
    {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        } else {
            $stmt->close();
            return true;
        }
    }

    /**
     * Deletes from the database.
     * @param string $sql
     * @param string|null $types
     * @param array|null $params
     * @param bool $deleteAll
     */
    protected function delete(string $sql, string $types = null, array $params = null, bool $deleteAll = false)
    {
        $stmt = $this->getConnection()->prepare($sql);
        if (!$deleteAll && $types && $params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Used to create an array of %LIKE% parameters for each column in a table,
     * this can be used in the search so the user can enter any type of column
     * string information to get a result in their search.
     * @param string $table
     * @param string $search
     * @param string $avoidID
     * @return string string to use in the query.
     */
    protected function builtMatchQuery(string $table, string $search, string $avoidID): string
    {
        $columns = $this->executeQuery("SELECT
                                   COLUMN_NAME
                                   FROM
                                   information_schema.COLUMNS
                                   WHERE TABLE_NAME = '$table'
                                   AND TABLE_SCHEMA = '9.0'");

        $searchTerms = array();
        while ($column = $columns->fetch_assoc()) {
            if ($column['COLUMN_NAME'] != $avoidID) {
                $searchTerms[] = $column['COLUMN_NAME'] . " LIKE '%$search%' ";
            }
        }

        return implode(" OR ", $searchTerms);
    }

    /**
     * This method is used to fetch the rows from a result aquired from the execueQuery() method.
     * @param mysqli_result $result
     * @return array of rows from the result.
     */
    protected function fetchResults(mysqli_result $result): array
    {
        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Get the connection instance.
     * @return MySQLi mysqli.
     */
    protected function getConnection(): MySQLi
    {
        return $this->conn;
    }
}
