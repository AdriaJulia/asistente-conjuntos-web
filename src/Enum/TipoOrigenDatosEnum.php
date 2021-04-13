<?php
namespace App\Enum;

/**
 * Descripcion: Enumerado de los tipos de orígenes  de datos admitidos
 */

class TipoOrigenDatosEnum {

  private static $types = [
      'Archivo' => self::ARCHIVO,
      'URL' => self::URL,
      'Base de datos' => self::BASEDATOS,
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