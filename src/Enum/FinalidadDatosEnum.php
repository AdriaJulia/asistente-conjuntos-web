<?php
namespace App\Enum;

/**
 * Descripcion: Enumerado del las opciones del combo finalidad de los datos
 */

class FinalidadDatosEnum {

  private static $types = [
      'Ciencia y Tecnología' => self::cienciatecnologia,
      'Comercio'  => self::comercio,
      'Cultura y ocio'  => self::culturaocio,
      'Demografía'  => self::demografia, 
      'Deporte'  => self::deporte,   
      'Economía'  => self::economia, 
      'Educación' => self::educacion,
      'Empleo' => self::empleo,
      'Energía'  => self::energia,
      'Hacienda'  => self::hacienda,
      'Industria'  => self::industria, 
      'Legislación y justicia'  => self::legislacionjusticia,   
      'Medio Ambiente'  => self::medioambiente,  
      'Medio rural' => self::medioruralpesca,
      'Salud' => self::salud,
      'Sector público'  => self::sectorpublico,
      'Seguridad'  => self::seguridad,
      'Sociedad y bienestar'  => self::sociedadbienestar, 
      'Transporte'  => self::transporte,   
      'Turismo'  => self::turismo, 
      'Urbanismo e infraestructuras'  => self::urbanismoinfraestructuras,   
      'Vivienda'  => self::vivienda,   
    ];
  
  const cienciatecnologia = "ciencia-tecnologia";
  const comercio = "comercio";
  const culturaocio = "cultura-ocio";
  const demografia = "demografia";
  const deporte = "deporte";
  const economia = "economia";
  const educacion = "educacion";
  const empleo = "empleo";
  const energia = "energia";
  const hacienda = "hacienda";
  const industria = "industria";
  const legislacionjusticia = "legislacion-justicia";
  const medioambiente = "medio-ambiente";
  const medioruralpesca = "medio-rural-pesca";
  const salud = "salud";
  const sectorpublico = "sector-publico";
  const seguridad = "seguridad";
  const sociedadbienestar = "sociedad-bienestar";
  const transporte = "transporte";
  const turismo = "turismo";
  const urbanismoinfraestructuras = "urbanismo-infraestructuras";
  const vivienda = "vivienda";
  

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