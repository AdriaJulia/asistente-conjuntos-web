<?php
namespace App\Enum;

/**
 * Descripcion: Enumerado de las extensiones soportadas 
 */

class TipoExtensionEnum {

  private static $types = [
      'CSV' => self::CSV,
      'XLS' => self::XLS,
      'XML' => self::XML,
      'JSON'  => self::JSON,
    ];
    
  const CSV = "CSV";
  const XLS = "XLS";
  const XML = "XML";
  const JSON = "JSON";

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