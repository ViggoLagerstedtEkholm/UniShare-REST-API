<?php
namespace App\Models\MVCModels;

/**
 * Model for carrying course data.
 * @author Viggo Lagestedt Ekholm
 */
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
