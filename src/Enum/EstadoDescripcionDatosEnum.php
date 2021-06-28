<?php
namespace App\Enum;

/**
 * Descripcion: Enumerado del estado del conjunto de datos 
 */
class EstadoDescripcionDatosEnum {

  private static $types = [
      'BORRADOR' => self::BORRADOR,
      'EN_ESPERA_VALIDACION' => self::EN_ESPERA_VALIDACION,
      'EN_ESPERA_MODIFICACION' => self::EN_ESPERA_MODIFICACION,
      'VALIDADO'  => self::VALIDADO,
      'DESECHADO'  => self::DESECHADO,
      'EN_CORRECCION'  => self::EN_CORRECCION,   
    ];
   
  const BORRADOR = "BORRADOR";
  const EN_ESPERA_VALIDACION = "EN_ESPERA_VALIDACION"; 
  const EN_ESPERA_MODIFICACION= "EN_ESPERA_MODIFICACION";
  const VALIDADO = "VALIDADO"; 
  const DESECHADO = "DESECHADO"; 
  const EN_CORRECCION = "EN_CORRECCION"; 

  const BORRADOR_KEY = "borrador";
  const EN_ESPERA_VALIDACION_KEY = "espera"; 
  const EN_ESPERA_MODIFICACION_KEY= "espera";
  const VALIDADO_KEY = "validado"; 
  const DESECHADO_KEY = "desechado"; 
  const EN_CORRECCION_KEY = "correccion"; 

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