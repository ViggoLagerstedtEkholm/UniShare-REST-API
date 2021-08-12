<?php
namespace App\Models\Templates;

class Course extends Model{
  public int $ID;
  public string $name;
  public float $credits;
  public float $duration;
  public string $added = "TODO";
  public string $field_of_study = "TODO";
}
