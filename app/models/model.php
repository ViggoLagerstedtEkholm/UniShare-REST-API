<?php
namespace App\Models;

abstract class Model{
  public function populateAttributes($data){
    foreach($data as $key => $value){
      if(property_exists($this, $key)){
        $this->{$key} = $value;
      }
    }
  }
}
