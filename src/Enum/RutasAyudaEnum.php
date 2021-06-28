<?php
namespace App\Enum;

/**
 * Descripcion: Enumerado de las rutas de la ayuda según funcionalidad
 */


class RutasAyudaEnum {

  private static $types = [
      'DESCRIPCION_DISTRIBUCION' => self::DESCRIPCION_DISTRIBUCION, 
      'DESCRIPCION_CONTENIDO' => self::DESCRIPCION_CONTENIDO, 
      'DESCRIPCION_CONTEXTO' => self::DESCRIPCION_CONTEXTO, 
      'ORIGEN_DATOS_URL' => self::ORIGEN_DATOS_URL, 
      'ORIGEN_DATOS_FILE' => self::ORIGEN_DATOS_FILE, 
      'ORIGEN_DATOS_DB' => self::ORIGEN_DATOS_DB, 
      'ALINEACION_EI2A' => self::ALINEACION_EI2A, 
      'ALINEACION_XML' => self::ALINEACION_XML,   
      'LISTADO_DESCRIPCION' => self::LISTADO_DESCRIPCION, 
      'FICHA_DESCRIPCION' => self::FICHA_DESCRIPCION,
    ];
   
  const DESCRIPCION_DISTRIBUCION = "paso0_nuevo_conjunto";
  const DESCRIPCION_CONTENIDO= "paso1_contenido";
  const DESCRIPCION_CONTEXTO= "paso2_contexto";

  const ORIGEN_DATOS_FILE = "paso3_origen_archivo";
  const ORIGEN_DATOS_URL = "paso3_origen_url";
  const ORIGEN_DATOS_DB = "paso3_origen_basedatos";

  const ALINEACION_EI2A = "paso4_integracion_seleccion";
  const ALINEACION_XML = "paso4_integracion_xml";

  const LISTADO_DESCRIPCION = "listado_info";
  const FICHA_DESCRIPCION = "ficha_info";


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