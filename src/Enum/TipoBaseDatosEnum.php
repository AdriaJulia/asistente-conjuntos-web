<?php
namespace App\Enum;

/**
 * Descripcion: Enumerado de los tipos BD
 */

class TipoBaseDatosEnum {

  private static $types = [
      'SQLSERVER' => self::SQLSERVER,
      'MYSQL' => self::MYSQL,
      'ORACLE' => self::ORACLE,
      'POSTGRESQL'  => self::POSTGRESQL,
    ];
  const SQLSERVER = "SQLSERVER";
  const MYSQL = "MYSQL";
  const ORACLE = "ORACLE";
  const POSTGRESQL = "POSTGRESQL";

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