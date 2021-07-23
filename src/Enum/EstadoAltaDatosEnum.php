<?php
namespace App\Enum;

/**
 * Descripción: Enumerado del estado del asistente 
 */
class EstadoAltaDatosEnum {

  private static $types = [

    'PASO1' => self::PASO1,
    'PASO2' => self::PASO2,
    'ORIGEN_URL'  => self::ORIGEN_URL,
    'ORIGEN_FILE'  => self::ORIGEN_FILE,
    'ORIGEN_DB'  => self::ORIGEN_DB,
    'ALINEACION'  => self::ALINEACION,   
  ];
 
  const PASO1 = "1.1 descripcion";
  const PASO2 = "1.2 descripcion";
  const ORIGEN_URL = "2: origen url";
  const ORIGEN_FILE = "2: origen file";
  const ORIGEN_DB = "2: origen database";
  const ALINEACION = "3: alineacion";

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

