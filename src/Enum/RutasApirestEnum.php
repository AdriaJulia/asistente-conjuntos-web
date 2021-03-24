<?php
namespace App\Enum;

/**
 * Descripcion: Enumerado de las rutas para llamar ala APirest según funcionalidad
 */

class RutasApirestEnum {

  private static $types = [
      'DESCRIPCION_DATOS' => self::DESCRIPCION_DATOS, 
      'DESCRIPCION_DATOS_POST_WORKFLOW' => self::DESCRIPCION_DATOS_POST_WORKFLOW, 
      'ORIGEN_DATOS_GET' => self::ORIGEN_DATOS, 
      'ORIGEN_DATOS_FICHA_GET' => self::ORIGEN_DATOS_FICHA, 
      'ORIGEN_DATOS_POST_INSERTA_DATABASE' => self::ORIGEN_DATOS_POST_INSERTA_DATABASE, 
      'ORIGEN_DATOS_POST_TEST_DATABASE' => self::ORIGEN_DATOS_POST_TEST_DATABASE, 
      'ORIGEN_DATOS_POST_ACTUALIZA_DATABASE' => self::ORIGEN_DATOS_POST_ACTUALIZA_DATABASE, 
      'ORIGEN_DATOS_POST_INSERTA_DATA' => self::ORIGEN_DATOS_POST_INSERTA_DATA, 
      'ORIGEN_DATOS_POST_TEST_DATA' => self::ORIGEN_DATOS_POST_TEST_DATA, 
      'ORIGEN_DATOS_POST_ACTUALIZA_DATA' => self::ORIGEN_DATOS_POST_ACTUALIZA_DATA,  
      'ORIGEN_DATOS_POST_ACTUALIZA_ALINEACION' => self::ORIGEN_DATOS_POST_ACTUALIZA_ALINEACION, 
      'USUARIO_LOGIN_CHECK_POST' => self::USUARIO_LOGIN_CHECK_POST, 
      'USUARIO_REGISTER_POST' => self::USUARIO_REGISTER_POST,
      'SOPORTE_USUARIOS' => self::SOPORTE_USUARIOS,
    ];
   

  const DESCRIPCION_DATOS = "/api/v1/descripciondatos";
  const DESCRIPCION_DATOS_POST_WORKFLOW = "/api/v1/descripciondatos/workflow";

  const ORIGEN_DATOS= "/api/v1/origendatos";
  const ORIGEN_DATOS_POST_TEST_DATABASE = "/api/v1/origendatos/database/test";
  const ORIGEN_DATOS_POST_INSERTA_DATABASE = "/api/v1/origendatos/database";
  const ORIGEN_DATOS_POST_ACTUALIZA_DATABASE = "/api/v1/origendatos/database";

  const ORIGEN_DATOS_POST_TEST_DATA = "/api/v1/origendatos/data/test";
  const ORIGEN_DATOS_POST_INSERTA_DATA = "/api/v1/origendatos/data";
  const ORIGEN_DATOS_POST_ACTUALIZA_DATA = "/api/v1/origendatos/data";

  const ORIGEN_DATOS_POST_ACTUALIZA_ALINEACION = "/api/v1/origendatos/alineacion";

  const USUARIO_LOGIN_CHECK_POST = "/api/login_check";
  const USUARIO_REGISTER_POST = "/api/register";

  const ORIGEN_DATOS_FICHA = "/api/v1/origendatos/datosficha";

  const SOPORTE_USUARIOS = "/api/v1/ayuda/soporte";

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