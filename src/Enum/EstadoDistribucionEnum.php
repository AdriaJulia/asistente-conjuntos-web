<?php
namespace App\Enum;

/**
 * Descripcion: Enumerado del estado del asistente 
 */
class EstadoDistribucionEnum {

  private static $types = [
      'PADRE' => self::PADRE,
      'SIN_HIJOS' => self::SIN_HIJOS,
  ];

  const PADRE = 0;
  const SIN_HIJOS = -1;

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