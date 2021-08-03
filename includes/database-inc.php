<?php
class Database{
  private $serverName;
  private $dbUserName;
  private $dbPassword;
  private $dbName;
  private $conn;

  function __construct($serverName, $dbUserName, $dbPassword, $dbName){
    $this->serverName = $serverName;
    $this->dbUserName = $dbUserName;
    $this->dbPassword = $dbPassword;
    $this->dbName = $dbName;
    $this->conn = mysqli_connect($serverName, $dbUserName, $dbPassword, $dbName);

    if(!$this->conn){
      die("Connection failed: " . mysqli_connect_error());
    }
  }

  public function close(){
    $this->conn -> close();
  }

  public function getServerName(){
		return $this->serverName;
	}

	public function getDbUserName(){
		return $this->dbUserName;
	}

	public function getDbPassword(){
		return $this->dbPassword;
	}

	public function getDbName(){
		return $this->dbName;
	}

	public function getConn(){
		return $this->conn;
	}
}
