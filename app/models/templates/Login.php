<?php
namespace App\Models\Templates;

class Login extends Model{
  public string $email;
  public string $password;
  public string $rememberMe = "off";
}
