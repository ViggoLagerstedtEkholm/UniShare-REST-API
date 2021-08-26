<?php
namespace App\models\MVCModels;

/**
 * Interface for validation of user input.
 * @author Viggo Lagestedt Ekholm
 */
interface IValidate {
  public function validate(array $params);
}
