<?php
namespace App\Models\Templates;

class Project extends Model{
  public int $ID;
  public string $name;
  public string $description;
  public string $link;
  public $image;
  public $custom;
  public $customCheck;
  public $added;
}
