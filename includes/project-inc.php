<?php
class Project {
  private $ID;
  private $name;
  private $description;
  private $link;
  private $image;

  function __construct($name, $description, $link, $image){
    $this->name = $name;
    $this->description = $description;
    $this->link = $link;
    $this->image = $image;
  }

  function setID($ID){
    $this->ID = $ID;
  }

  function getID(){
    return $this->ID;
  }

  function getName(){
    return $this->name;
  }

  function getDescription(){
    return $this->description;
  }

  function getLink(){
    return $this->link;
  }

  function getImage(){
    return $this->image;
  }
}
