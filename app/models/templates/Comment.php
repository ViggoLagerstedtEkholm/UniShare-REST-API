<?php
namespace App\Models\Templates;

class Comment extends Model{
  public int $ID;
  public string $text;
  public string $date;
  public int $author;
  public int $profile;
  public string $added;
  public $image;
  public string $display_name;
}
