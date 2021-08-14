<?php
namespace App\Models\Templates;

class Degree extends Model{
  public int $ID;
  public string $name;
  public string $field_of_study;
  public string $start_date;
  public string $end_date;
}
