<?php
namespace App\Models\MVCModels;

class Course{
  public int $ID;
  public float $credits;
  public float $duration;
  public $rating;
  public string $name;
  public string $added;
  public string $city;
  public string $country;
  public string $university;
  public string $description;
  public bool $existsInActiveDegree = false;
}
