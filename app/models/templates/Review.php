<?php
namespace App\Models\Templates;

class Review{
  public int $ID;
  public string $userDisplayName;
  public $userImage;
  public int $userID;
  public int $courseID;
  public int $fulfilling;
  public int $environment;
  public int $difficulty;
  public int $grading;
  public int $litterature;
  public int $overall;
  public string $text;
}
