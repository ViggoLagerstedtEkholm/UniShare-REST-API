<?php
namespace App\Models\MVCModels;
use App\Models\Templates\Forum;
use App\Models\MVCModels\Database;
use App\Core\Session;

class Forums extends Database implements IValidate{
  public function validate($params){
    $errors = array();
    return $errors;
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

     $forums = array();
     while( $row = $result->fetch_array())
     {
         $forum = new Forum();
         $forum->ID = $row['forumID'];
         $forum->title = $row['title'];
         $forum->topic = $row['topic'];
         $forum->views = $row['views'];

         $forums[] = $forum;
     }
     return $forums;
   }
}
