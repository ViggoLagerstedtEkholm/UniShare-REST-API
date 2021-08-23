<?php
namespace App\Models\MVCModels;
use App\Models\MVCModels\Database;
use App\Core\Session;
use App\Includes\Validate;

class Forums extends Database implements IValidate{
  public function validate($params){
    $errors = array();
    
    if(Validate::arrayHasEmptyValue($params) === true){
      $errors[] = EMPTY_FIELDS;
    }

    return $errors;
  }
  
  function getForum($forumID){
    $sql = "SELECT * FROM forum WHERE forumID = ?;";
    $result = $this->executeQuery($sql, 'i', array($forumID));
    return $result->fetch_assoc();
  }
  
  function insertForum($params){
    $userID = Session::get(SESSION_USERID);
    $inserted = false;
    try {
        $this->getConnection()->begin_transaction();
        date_default_timezone_set("Europe/Stockholm");
        $date = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO forum (title, topic, creator, created) values(?,?,?,?);";
        $inserted = $this->insertOrUpdate($sql, 'ssis', array($params['title'], $params['topic'], $userID, $date));
        $forumID = $this->getConnection()->insert_id;
  
        if(!$inserted){
          $this->getConnection()->rollback();
        }
        
        
        $sql = "INSERT INTO posts(userID, forumID, text, date) values(?,?,?,?);";
        $inserted = $this->insertOrUpdate($sql, 'iiss', array($userID, $forumID, $params["text"], $date));

        if(!$inserted){
          $this->getConnection()->rollback();
        }
        
        $this->getConnection()->commit();
    } catch (\Throwable $e) {
        $this->getConnection()->rollback();
        throw $e;
    }

    if($inserted){
      return $forumID;
    }else{
      return null;
    }
  }
  
  function addViews($forumID){
    $forum = $this->getForum($forumID);
    $views = $forum["views"];
    $updatedViews = $views + 1;
    $sql = "UPDATE forum SET views =? WHERE forumID = ?;";
    $this->insertOrUpdate($sql, 'ii', array($updatedViews , $forumID));
  }
  
  function getTOP10Forums(){
    $sql = "SELECT * 
            FROM forum 
            ORDER BY views 
            DESC
            LIMIT 10;";
    $result = $this->executeQuery($sql);
    return $this->fetchResults($result);
  }
    
  function getForumCount(){
    $sql = "SELECT Count(*) FROM forum";
    $result = $this->executeQuery($sql);
    return $result->fetch_assoc()["Count(*)"];
  }

  function getForumCountSearch($search){
    $MATCH = $this->builtMatchQuery('forum', $search, 'forumID');
    $sql = "SELECT Count(*) FROM forum WHERE $MATCH";
    $result = $this->executeQuery($sql);
    return $result->fetch_assoc()["Count(*)"];
  }

  function fetchForumsSearch($from, $to, $option, $filterOrder, $search = null){
    $option ?? $option = "title";
    $filterOrder ?? $filterOrder = "DESC";

    if(!is_null($search)){
       $MATCH = $this->builtMatchQuery('forum', $search, 'forumID');
       $searchQuery = "SELECT *
                       FROM forum
                       WHERE $MATCH
                       ORDER BY $option $filterOrder
                       LIMIT ?, ?;";

      $result = $this->executeQuery($searchQuery, 'ii', array($from, $to));
    }else{
      $searchQuery = "SELECT *
                      FROM forum
                      ORDER BY $option $filterOrder
                      LIMIT ?, ?;";

      $result = $this->executeQuery($searchQuery, 'ii', array($from, $to));
    }
    
     return $this->fetchResults($result);
   }
}
