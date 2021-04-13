<?php
namespace App\Enum;

/**
 * Descripcion: Enumerado del estado del asistente 
 */
class PrefijosOntologiasEnum {

  public static $types = [
    
    'gtfs' => self::gtfs,
    'foaf' => self::foaf,
    'prov' => self::prov,
    'ei2a' => self::ei2a,
    'sosa' => self::sosa,
    'eli' => self::eli,
    'org' => self::org,
    'rdfs' => self::rdfs,
    'owl' => self::owl,
    'bio' => self::bio,
    'vcard' => self::vcard,
    'schema' => self::schema,
    'time' => self::time,
    'dcterm' => self::dcterm,
    'ocds' => self::ocds,
    'rdf' => self::rdf,
    'xsd' => self::xsd,
    'skos' => self::skos,
    'wgs84_pos' => self::wgs84_pos,
    'dc' => self::dc
  ];
 
 const gtfs="http://vocab.gtfs.org/terms#";
 const foaf="http://xmlns.com/foaf/0.1/";
 const prov="http://www.w3.org/ns/prov#";
 const ei2a="http://opendata.aragon.es/def/ei2a#";
 const sosa="http://www.w3.org/ns/sosa/";
 const eli="http://data.europa.eu/eli/ontology/";
 const org="http://www.w3.org/ns/org#";
 const rdfs="http://www.w3.org/2000/01/rdf-schema#";
 const owl="http://www.w3.org/2002/07/owl#";
 const bio="http://purl.org/vocab/bio/0.1/";
 const vcard="http://www.w3.org/2006/vcard/ns#";
 const schema="http://schema.org/";
 const time="http://www.w3.org/2006/time#";
 const dcterm="http://purl.org/dc/terms/";
 const ocds="http://data.tbfy.eu/ontology/ocds#";
 const rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#";
 const xsd="http://www.w3.org/2001/XMLSchema#";
 const skos="http://www.w3.org/2004/02/skos/core#";
 const wgs84_pos="http://www.w3.org/2003/01/geo/wgs84_pos#";
 const dc="http://purl.org/dc/elements/1.1/";

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
