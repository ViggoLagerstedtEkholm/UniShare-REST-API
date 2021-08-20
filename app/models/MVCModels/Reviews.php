<?php
namespace App\Models\MVCModels;
use App\Models\MVCModels\Database;
use App\Models\Templates\Request;
use App\Includes\Validate;

class Reviews extends Database implements IValidate{
  public function validate($params){
    $errors = array();
    
    if(Validate::arrayHasEmptyValue($params) === true){
      $errors[] = EMPTY_FIELDS;
    }
    
    if(strlen($params["text"]) < 200){
      $errors[] = "DESCRIPTION_CHARS_NOT_ENOUGH";
    }

    return $errors;
  }
}
