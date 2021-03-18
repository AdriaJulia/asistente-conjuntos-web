<?php
namespace App\Enum;

/**
 * Descripcion: Enumerado de las rutas de la ayuda según funcionalidad
 */


class RutasAyudaEnum {

  private static $types = [
      'DESCRIPCION_PASO11' => self::DESCRIPCION_PASO11, 
      'DESCRIPCION_PASO12' => self::DESCRIPCION_PASO12, 
      'DESCRIPCION_PASO13' => self::DESCRIPCION_PASO13, 
      'ORIGEN_DATOS_URL' => self::ORIGEN_DATOS_URL, 
      'ORIGEN_DATOS_FILE' => self::ORIGEN_DATOS_FILE, 
      'ORIGEN_DATOS_DB' => self::ORIGEN_DATOS_DB, 
      'ALINEACION_DATOS' => self::ALINEACION_DATOS,  
      'LISTADO_GENERAL' => self::LISTADO_GENERAL, 
      'LISTADO_ACCIONES' => self::LISTADO_ACCIONES, 
      'FICHA_GENERAL' => self::FICHA_GENERAL,
      'FICHA_ACCIONES' => self::FICHA_ACCIONES,    
    ];
   
  const DESCRIPCION_PASO11= "11";
  const DESCRIPCION_PASO12 = "12";
  const DESCRIPCION_PASO13= "13";

  const ORIGEN_DATOS_FILE = "21";
  const ORIGEN_DATOS_URL = "22";
  const ORIGEN_DATOS_DB = "23";

  const ALINEACION_DATOS = "31";

  const LISTADO_GENERAL = "41";
  const LISTADO_ACCIONES = "42";

  const FICHA_GENERAL = "51";
  const FICHA_ACCIONES = "52";



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