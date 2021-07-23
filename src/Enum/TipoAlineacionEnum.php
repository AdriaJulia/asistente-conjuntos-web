<?php
namespace App\Enum;

/**
 * Descripción: Enumerado de los tipos de orígenes  de datos admitidos
 */

class TipoAlineacionEnum {

  private static $types = [
    'CAMPOS' => self::CAMPOS,
    'XML' => self::XML,
  ];
  const CAMPOS = "CAMPOS";
  const XML = "XML";

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