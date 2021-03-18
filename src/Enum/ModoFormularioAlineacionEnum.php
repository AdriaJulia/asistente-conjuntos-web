<?php
namespace App\Enum;

/**
 * Descripcion: Enumerado del las opciones del PASO3 para finalizar el asistente 
 */

class ModoFormularioAlineacionEnum {

  private static $types = [
      'Guardar' => self::Guardar,
      'Omitir' => self::Omitir,
      'Seleccion'  => self::Seleccion,
    ];
    
  const Guardar = "guardar";
  const Omitir = "omitir";
  const Seleccion = "seleccion";

  
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