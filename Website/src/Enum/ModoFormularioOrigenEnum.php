<?php
namespace App\Enum;

class ModoFormularioOrigenEnum {

  private static $types = [
      'Test' => self::Test,
      'Insert' => self::Insert
    ];
    
  const Test = "test";
  const Insert = "insert";

  
  public static function getValues(){
      return self::$types;
  }
  public static function fromString($index){
      return self::$types[$index];
  }
  public static function toString($value){
      return array_search($value, self::$types);
  }
}