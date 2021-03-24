<?php
namespace App\Enum;

/**
 * Descripcion: Enumerado del estado del asistente 
 */
class EstadoAltaDatosEnum {

  private static $types = [
    
    'paso1' => self::paso1,
    'paso2' => self::paso2,
    'paso3'  => self::paso3,
    'origen_ulr'  => self::origen_url,
    'origen_file'  => self::origen_file,
    'origen_database'  => self::origen_database,
    'alineacion'  => self::alineacion,   
  ];
 
  const paso1 = "1.1 descripcion";
  const paso2 = "1.2 descripcion";
  const paso3 = "1.3 descripcion";
  const origen_url = "2: origen url";
  const origen_file = "2: origen file";
  const origen_database = "2: origen database";
  const alineacion = "3: alineacion";

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