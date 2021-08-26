<?php
namespace App\models\MVCModels;

/**
 * Model for carrying course data.
 * @author Viggo Lagestedt Ekholm
 */
class Course{
  public int $ID;
  public float $credits;
  public float $duration;
  public string $rating;
  public string $name;
  public string $added;
  public string $city;
  public string $country;
  public string $university;
  public string $description;
  public bool $existsInActiveDegree = false;
  public float $totalCredits;
}
