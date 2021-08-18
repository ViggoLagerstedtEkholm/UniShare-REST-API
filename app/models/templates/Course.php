<?php
namespace App\Models\Templates;

class Course extends Model{
  public int $ID;
  public float $credits;
  public float $duration;
  public $rating;
  public string $name;
  public string $added;
  public string $city;
  public string $country;
  public string $university;
  public bool $existsInActiveDegree = false;
}
