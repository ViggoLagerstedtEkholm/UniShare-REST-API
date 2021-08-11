<?php
namespace App\Models\Templates;

class Register extends Model{
  public string $first_name;
  public string $last_name;
  public string $email;
  public string $display_name;
  public string $password;
  public string $password_repeat;
}
