<?php
namespace App\Enum;

/**
 * Descripcion: Enumerado del las opciones del combo  frecuencia actualización 
 */

class FrecuenciaActualizacionEnum {

  private static $types = [
      'Anual' => self::Anual,
      'Semestral' => self::Semestral,
      'Cuatrimestral'  => self::Cuatrimestral,
      'Trimestral'  => self::Trimestral,
      'Mensual'  => self::Mensual, 
      'Diaria'  => self::Diaria,   
      'Instantánea'  => self::Instantanea  
    ];
    
  const Anual = "Anual";
  const Semestral = "Semestral";
  const Cuatrimestral = "Cuatrimestral";
  const Trimestral = "Trimestral";
  const Mensual = "Mensual";
  const Diaria = "Diaria";
  const Instantanea = "Instantánea";
  
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