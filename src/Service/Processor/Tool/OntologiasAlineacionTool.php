<?php

namespace App\Service\Processor\Tool; 


use App\Enum\PrefijosOntologiasEnum;
/*
 * Descripción: Es la clase aparece como conjunto de utilidades para generar las ontologia que se seleccionan en
 *              El formulario de alineación (paso 3)
 *              Las ontologias principales, las que seleccionamos en el primer combo se listan del archivo assistant_mapping.xml
 *              Las campos (tributos de las ontologias ), se extraen del archivo EI2A.owl 
 *              Los atributos estan en varios subniveles
 */              
class OntologiasAlineacionTool
{

     private $simplexml;
     private $simpleowl;
     public function __construct(){
          $sassistantMapping = str_replace($_SERVER['SCRIPT_URL'] , "", $_SERVER['SCRIPT_URI']) . "/resources/assistant_mapping.xml";   
          $this->simplexml = simplexml_load_file($sassistantMapping);
     }



   /** 
     * Descripción: Devuelve las ontologias principales del paso3
     */  
    public function GetOntologias():array {
         $Ontologiasview = array();
         foreach($this->simplexml->Entity as $Entry) {
            $type = $this->getAtributeXML($Entry,"rdf","type");
            $label = $this->getAtributeXML($Entry,"rdfs","label");
            $Ontologiasview[$label] = $type;
         }             
         ksort($Ontologiasview);
         return $Ontologiasview;
     }

   /** 
     * Descripción: Devuelve los atributos de una ontologia principal
     *              Se rastrean los atributos de la ontologia  y todas sus subentifdades de manera recursiva 
     * 
     * Parametros: ontologia principal  
     */ 
    public function GetOntologia($ontologia):array {
      $nombreOntologia = $this->DameNombreOntologia($ontologia);
      $ie2a = str_replace($_SERVER['SCRIPT_URL'] , "", $_SERVER['SCRIPT_URI']) . "/resources/EI2A.owl";
      $this->simpleowl = simplexml_load_file($ie2a);
      $ontologiaExtendida = $this->ExtiendeEntidad($ontologia);
      $ontologiaPrincipal = array();
      $Ontologiasview = array();
      $atributesPrincipales = array();
      //primero recogemos los tributos de la entidad principal
      foreach($this->simpleowl->children('owl',true)->Class as $Entry) {
         if ($this->getAtributeXML($Entry,"rdf","about") == $ontologiaExtendida) {
            $ontologiaPrincipal = $this->getAtributesEntidad($ontologia, $nombreOntologia);   
         }
      }
      //ahora recogemos las entidades secundarias del mapa
      $subentides = array();
      foreach($this->simplexml->Entity as $Entry) {
         if ($this->getAtributeXML($Entry,"rdf","type") == $ontologia) {
            $subentiadesEntarda = $this->DameSubEntidades($Entry,"","", array());
            foreach($subentiadesEntarda as $key=>$value) {
               $subentides[$key] = $value;
            }
         }
      }
      ksort($ontologiaPrincipal);
      //por cada una de las entidades secundarias recogemos los atributos igual que en la principal
      foreach($subentides as $keySecubdaria =>$valueSecundaria) { 
         //preparo los parametros para pasar a la funcion que recoge los atributos
         $ontologias = explode(">",$keySecubdaria);
         $ontologia =  $ontologias[count($ontologias) -1];
         $nombreOntologias = explode("> ",$valueSecundaria);
         $nombreOntologia = $nombreOntologias[count($nombreOntologias) -1];
         $ontologiaExtendida = $this->ExtiendeEntidad($ontologia);
         //recorro el archivo ontologico otra vez con las secudaria
         foreach($this->simpleowl->children('owl',true)->Class as $Entry) {
            if ($this->getAtributeXML($Entry,"rdf","about") == $ontologiaExtendida) {
               $ontologiasecundaria = $this->getAtributesEntidad($ontologia, $nombreOntologia);
               ksort($ontologiasecundaria);
               //ahora arreglo las etiquetas y las rutas ya que el principio de la secundaria se repite pon el final de la  primaria
               foreach($ontologiasecundaria as $key=>$value) {  
                  $keyJoin = str_replace($ontologia,"", $keySecubdaria) . $key; 
                  $ValueJoin = str_replace($nombreOntologia,"", $valueSecundaria) . $value;   
                  $ontologiaPrincipal[$keyJoin] = $ValueJoin;
               } 
            }
         }
      }
      return $ontologiaPrincipal;
   }

   /** 
     * Descripción: Devuelve el nombre (label) de la ontologia 
     * 
     * Parametros: ontologia principal prefijada  
     */ 
    private function DameSubEntidades($entidad, $atributePefijo, $titulo, $arraySubentidades): array 
    {
      
      $atributePefijo = empty($atributePefijo) ?  
                           $this->getAtributeXML($entidad,"rdf","type") : 
                                 $atributePefijo .">" . $this->getAtributeXML($entidad,"rdf","type"); 
      $titulo = empty($titulo) ? 
                  $this->getAtributeXML($entidad,"rdfs","label") :
                         $titulo . "> " .$this->getAtributeXML($entidad,"rdfs","label"); 
      if ($entidad->Children()) { 
         $propiedades = $entidad->Property;
         foreach($propiedades as $propiedad) { 
            $atributePefijo =  $atributePefijo . "#" .  ((array)$propiedad->attributes()[0])[0];
            $array = $this->DameSubEntidades($propiedad->Entity,$atributePefijo,$titulo, $arraySubentidades);
            foreach($array as $key=>$value)  {
               $arraySubentidades[$key] = $value;
            }
         }     
       } else {
         $arraySubentidades[$atributePefijo] = $titulo;
       } 

       return $arraySubentidades;
    } 

   /** 
     * Descripción: Devuelve el nombre (label) de la ontologia 
     * 
     * Parametros: ontologia principal prefijada  
     */ 
   private function DameNombreOntologia($ontologia): string 
   {
      $label = "";
      foreach($this->simplexml->Entity as $Entry) {
         if ($ontologia == $this->getAtributeXML($Entry,"rdf","type")) {
            $label = $this->getAtributeXML($Entry,"rdfs","label");
            break;
         }
      }
      return $label;
   }

   /** 
    * Descripción: Devuelve la entidad extendida por su prefijo
    * 
    * Parametros: ontologia prefijada
    */ 
   private function ExtiendeEntidad($ontologia) : string {
      $extendida = "";
      $separador = explode(":",$ontologia);
      if (count($separador) == 2){
         $entidad = $separador[1];
         $prefijo = PrefijosOntologiasEnum::fromString($separador[0]);
         $extendida = $prefijo . $entidad;
      }
      return $extendida;
   }

   /** 
    * Descripción: Nos devuelve el array de dominios que pertenece un atributo
    *
    * Parametros: atributo: el atributo a analizar
    */ 
   private function DameDominiosAtributo($atributo) : array {
      $dominios = array();
      $ClassP = $this->getNodesXML($atributo,'owl','Class');
      $unionOf = $this->getNodesXML($ClassP,'owl','unionOf');
      $Classs = $this->getNodesXML($unionOf,'owl','Class'); 
      foreach($Classs as  $Class){
         $Atributo = $this->getAtributeXML($Class,"rdf","about");
         if (!in_array($Atributo,$dominios)) {
            array_push($dominios,$Atributo);
         }
      }   
      return $dominios;
   }

   /** 
    * Descripción: Nos indica si el atributo esta en el dominio de la entidad
    *
    * Parametros: atributo: el atributo a analizar
    *             dominio:  el dominio a compara
    */ 
  private function AtributoPerteneceEntidad($atributo, $dominio) :bool {
      $pertenece = false;
      $entidad = $this->ExtiendeEntidad($dominio);
      $dominio = $this->getNodesXML($atributo,'rdfs','domain'); 
      if ($dominio) {
         $class = $this->getNodesXML($dominio,'owl','Class'); 
         if ($class) {
             $dominios = $this->DameDominiosAtributo($dominio);
             foreach($dominios as $dominiopropietario){
               if  (trim($entidad) == trim($dominiopropietario)) {
                  $pertenece = true;
               }
             }
         } else {
            $dominiopropietario = $this->getAtributeXML($dominio,"rdf","resource"); 
            $pertenece = (trim($entidad) == trim($dominiopropietario));
         }
      }
      return $pertenece;
  }

  /** 
   * Descripción: Devuelve los atributos de una ontologia principal
   *             
   * Parametros: ontologia principal  
   */ 
   private function getAtributesEntidad($Entidad,$NombreEntidad): array {
      $atributos = array();
      foreach($this->simpleowl->children('owl',true)->DatatypeProperty as $DatatypeProperty) {
         if ($this->AtributoPerteneceEntidad($DatatypeProperty,$Entidad)) {
            $Atributo = $this->getAtributeXML($DatatypeProperty,"rdf","about");
            $atributoFinal = $this->DamePropiedad($Atributo, $Entidad, "#");
            foreach($atributoFinal as $key => $value){
               if (array_search($value, $atributos) == 0) {
                  $atributos[$key] = $NombreEntidad . '> ' . $value;
               }
            }
         }
      }
      foreach($this->simpleowl->children('owl',true)->FunctionalProperty as $FunctionalProperty) {
         if ($this->AtributoPerteneceEntidad($FunctionalProperty,$Entidad)) {
            $Atributo = $this->getAtributeXML($FunctionalProperty,"rdf","about");
            $atributoFinal = $this->DamePropiedad($Atributo, $Entidad, "@");
            foreach($atributoFinal as $key => $value){
               if (array_search($value, $atributos) == 0) {
                  $atributos[$key] = $NombreEntidad . '> ' . $value;
               }
            }
         }
      }
      ksort($atributos);
      return $atributos;
   }  

  /** 
   * Descripción: Devuelve en clave valor el atributo y su valor para el combo
   *             
   * Parametros: atributo: atributo extendido  
   *             entidad: entidad prefijada      
   */ 
   private function DamePropiedad($atributo,$entidad,$separador): array {
      $AtributoPrefijado = $entidad . $separador .$this->AtributoPrefijado($atributo);
      $valor = "";
      switch ($separador) {
         case '#':
            foreach($this->simpleowl->Children('owl',true)->DatatypeProperty as $Entry) {
               if ($this->getAtributeXML($Entry,"rdf","about") == $atributo) {
                  $node = $Entry->Children('rdfs',true)->label;
                  $valor = ((array)iterator_to_array($node)['label'])[0];
                  break;
               }
            }
            break;
         case '@':
               foreach($this->simpleowl->Children('owl',true)->FunctionalProperty as $Entry) {
                  if ($this->getAtributeXML($Entry,"rdf","about") == $atributo) {
                     $node = $Entry->Children('rdfs',true)->label;
                     $valor = ((array)iterator_to_array($node)['label'])[0];
                     break;
                  }
               }
               break;         
         default:
            # code...
            break;
      }
      return [$AtributoPrefijado =>  $valor];
   }

   /** 
     * Descripción: Devuelve los atributos de una ontologia principal
     *             
     * 
     * Parametros: ontologia principal  
     */ 
   private function getAtributeXML($node, $pefix, $name ): string {
      return ((array)iterator_to_array($node->attributes($pefix,true))[$name])[0];
   }


   /** 
     * Descripción: Devuelve un tributo prefijado de uno extendido  
     * 
     * Parametros: ontologia principal prefijada  
    */ 
   private function AtributoPrefijado($atributo): string {
        $prefijado = "";
        foreach(PrefijosOntologiasEnum::$types as $clave => $valor) {
           if (!(strpos($atributo,$valor) === false)) {
              $nombre = str_replace($valor, "", $atributo);
              $prefijado = $clave . ":" . $nombre;
              break;
           }
        }
        return $prefijado;
   }


   /** 
     * Descripción: Devuelve la lista de nodos del tipo dado
     * 
     * Parametros: node: el nodo principal
     *             prefix: el prefijo que buscamos
     *             $name:  el tipo de nodo por su nombre
    */ 
   private function getNodesXML($node, $pefix, $name ) {
      switch ($name) {
         case 'subClassOf':
            $nodes =  $node->Children($pefix,true)->subClassOf;
         break;
         case 'Restriction':
            $nodes =  $node->Children($pefix,true)->Restriction;
         break;
         case 'onProperty':
            $nodes =  $node->Children($pefix,true)->onProperty;
         break;
         case 'DatatypeProperty':
            $nodes =  $node->Children($pefix,true)->DatatypeProperty;
         break;  
         case 'FunctionalProperty':
            $nodes =  $node->Children($pefix,true)->FunctionalProperty;
         break; 
         case 'domain':
            $nodes =  $node->Children($pefix,true)->domain;
         break;     
         case 'Class':
            $nodes =  $node->Children($pefix,true)->Class;
         break;   
         case 'unionOf':
            $nodes =  $node->Children($pefix,true)->unionOf;
         break;         
      }
      return $nodes;
   }

}