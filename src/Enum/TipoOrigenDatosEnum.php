<?php
namespace App\Enum;

class TipoOrigenDatosEnum {

  private static $types = [
      'ARCHIVO' => self::ARCHIVO,
      'URL' => self::URL,
      'BASEDATOS' => self::BASEDATOS,
    ];

  const ARCHIVO = "file";
  const URL = "url";
  const BASEDATOS = "database";

  
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