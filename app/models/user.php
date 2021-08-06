<?php
namespace App\Models;

class User{
  private $ID;
  private $first_name;
  private $last_name;
  private $email;
  private $display_name;
  private $password;
  private $password_repeat;
  private $image;
  private $last_online;
  private $visitors;

  function __construct($first_name, $last_name, $email, $display_name){
    $this->first_name = $first_name;
    $this->last_name = $last_name;
    $this->email = $email;
    $this->display_name = $display_name;
  }

  public function getFirst_name(){
		return $this->first_name;
	}

	public function setFirst_name($first_name){
		$this->first_name = $first_name;
	}

	public function getLast_name(){
		return $this->last_name;
	}

	public function setLast_name($last_name){
		$this->last_name = $last_name;
	}

	public function getEmail(){
		return $this->email;
	}

	public function setEmail($email){
		$this->email = $email;
	}

	public function getDisplay_name(){
		return $this->display_name;
	}

	public function setDisplay_name($display_name){
		$this->display_name = $display_name;
	}

	public function getPassword(){
		return $this->password;
	}

	public function setPassword($password){
		$this->password = $password;
	}

	public function getPassword_repeat(){
		return $this->password_repeat;
	}

	public function setPassword_repeat($password_repeat){
		$this->password_repeat = $password_repeat;
	}

  public function setID($ID){
    $this->ID = $ID;
  }

  public function getID(){
    return $this->ID;
  }

  public function getImage(){
    return $this->image;
  }

  public function setImage($image){
      $this->image = $image;
  }

  public function getLastOnline(){
    return $this->last_online;
  }

  public function setLastOnline($date){
    return $this->last_online = $date;
  }

  public function getVisitors(){
    return $this->visitors;
  }

  public function setVistiors($visitors){
    $this->visitors = $visitors;
  }
}
