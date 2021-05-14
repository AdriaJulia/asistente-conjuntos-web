<?php

namespace App\Service\Processor\Tool; 

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
     private $prefijosOntologias;

     public function __construct(){
          $sassistantMapping = str_replace($_SERVER['SCRIPT_URL'] , "", $_SERVER['SCRIPT_URI']) . "/resources/assistant_mapping.xml";   
          $this->simplexml = simplexml_load_file($sassistantMapping);
          $ie2a = str_replace($_SERVER['SCRIPT_URL'] , "", $_SERVER['SCRIPT_URI']) . "/resources/EI2A.owl";
          $this->simpleowl = simplexml_load_file($ie2a);
     }


   /** 
   * Descripción: Devuelve las ontologias principales del paso3
   */  
   public function GetPrefijosOntologias() {
      if (!isset($this->prefijosOntologias)) {
         $this->prefijosOntologias = $this->simplexml->getDocNamespaces(); 
      }     
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
      $ontologiaExtendida = $this->ExtiendeEntidad($ontologia);
      //primero recogemos los tributos de la entidad principal
      $ontologiaPrincipal = $this->getAtributosEntidadHerencia($ontologia,$ontologiaExtendida,$nombreOntologia,array(),"","");
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

         $ontologiaPrincipal = $this->getAtributosEntidadHerencia($ontologia,$ontologiaExtendida,$nombreOntologia,$ontologiaPrincipal, $keySecubdaria, $valueSecundaria);
      }
      return $ontologiaPrincipal;
   }

   /** 
     * Descripción: Devuelve el array de atributos en formato clave valor de un nodo y sus padres en herecia
     * 
     * Parametros: 
     *             ontologia:  nombre ontologia contraida con prefijo
     *             ontologiaExtendida: nombre ontologia extendida url completa
     *             nombreOntologia: Nombre de la ontologia para las etiquetas
     *             ontologiaJoin: array de la ontologia padre si el el padre es un array vacío
     *             keySecubdaria: la key de la ontologia secundaria para formar la cadena seguimiento 
     *             valueSecundaria: el valor la key de la ontologia secundaria para formar la cadena seguimiento    
   */ 

   public function getAtributosEntidadHerencia($ontologia, $ontologiaExtendida, $nombreOntologia, $ontologiaJoin, $keySecubdaria, $valueSecundaria): array {
      $ontologiaPrincipal = array();
      foreach($this->simpleowl->children('owl',true)->Class as $Entry) {
         if ($this->getAtributeXML($Entry,"rdf","about") == $ontologiaExtendida) {
            $ontologiasPadre = $this->DameEntidadesPadre($Entry);
            foreach($ontologiasPadre as $ontologiaPadre) {
               $ontologiaPadreComprimida = $this->ComprimeEntidad($ontologiaPadre);
               $ontologiaPrincipal = $this->getAtributesEntidad($ontologia, $nombreOntologia);   
               foreach($this->simpleowl->children('owl',true)->Class as $Entry) {
                  if ($this->getAtributeXML($Entry,"rdf","about") == $ontologiaPadre) {
                     $ontologiasPrincipalPadre = $this->getAtributesEntidad($ontologiaPadreComprimida, $nombreOntologia); 
                     foreach($ontologiasPrincipalPadre as $key => $value) {
                        $ontologiaPrincipal[$key] = $value;
                     }   
                  }
               }
            }
            ksort($ontologiaPrincipal);
         }
         foreach($ontologiaPrincipal as $key=>$value) {  
            $keyJoin = str_replace($ontologia,"", $keySecubdaria) . $key; 
            $ValueJoin = str_replace($nombreOntologia,"", $valueSecundaria) . $value;   
            $ontologiaJoin[$keyJoin] = $ValueJoin;
         } 
      }

      return $ontologiaJoin;
   }

    /** 
     * Descripción: Devuelve la descripcion y el enlace del la ontologia

     * 
     * Parametros: ontologia principal  
     */ 
   public function GetDescricionEnlaceOntologia($ontologia): array {
      $descripcion = null;
      $enalce = null;
      foreach($this->simplexml->Entity as $Entry) {
         if ($ontologia == $this->getAtributeXML($Entry,"rdf","type")) {
               $descripcion = [$this->getAtributeXML($Entry,"rdfs","comment")]; 
               $enalce = [$this->getAtributeXML($Entry,"rdf","about")];  
            break;
         }
      }
      return [$descripcion,$enalce];
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
                         $titulo . " > " .$this->getAtributeXML($entidad,"rdfs","label"); 
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
     * Descripción: Loas ontologias Padre de la principal
     * 
     * Parametros: ontologia principal prefijada  
     */ 
    private function DameEntidadesPadre($entidad): array 
    {
      $entidadesPadre = [];
      $count =0;
      $clasesPadre =  $this->getNodesXML($entidad,"rdfs","subClassOf");
      if ($clasesPadre) {
         foreach((array)iterator_to_array($clasesPadre) as $subClassOf) {
            $clasePadre =  $this->getAtributeXML($subClassOf,"rdf","resource");
            $entidadesPadre[$count ] = $clasePadre;
            $count++;
         } 
      }    
      return $entidadesPadre;
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
      $this->GetPrefijosOntologias();
      if (count($separador) == 2){
         $entidad = $separador[1];
         $prefijo = $this->prefijosOntologias[$separador[0]];
         $extendida = $prefijo . $entidad;
      }
      return $extendida;
   }

   /** 
    * Descripción: Devuelve la entidad comprimida por su prefijo
    * 
    * Parametros: ontologiaextendida : nobre de la entidad extendido
    */ 
    private function ComprimeEntidad($ontologiaextendida) : string {
      $comprimida = "";
      $this->GetPrefijosOntologias();
      foreach($this->prefijosOntologias as $key=>$value) {
        if (strpos($ontologiaextendida,$value) !== false){
         $entidad = trim(str_replace($value,"",$ontologiaextendida));
         $comprimida= "{$key}:{$entidad}";
         break;
        }
      }
      return $comprimida;
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
                  $atributos[$key] = $NombreEntidad . ' > ' . $value;
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
                  $atributos[$key] = $NombreEntidad . ' > ' . $value;
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
                     if (count(iterator_to_array($Entry->Children('rdfs',true)->label))) {
                        $node = $Entry->Children('rdfs',true)->label;
                        $valor = ((array)iterator_to_array($node)['label'])[0];
                     } else if(count(iterator_to_array($Entry->Children('rdfs',true)->comment))) {
                        $node = $Entry->Children('rdfs',true)->comment;
                        $valor = ((array)iterator_to_array($node)['comment'])[0];
                     } else {
                        $valor = "Error";
                     }
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
      $atribute = "";
      if (in_array($name,array_keys((array)iterator_to_array($node->attributes($pefix,true))))) {
         $atribute= ((array)iterator_to_array($node->attributes($pefix,true))[$name])[0];
      }
      return $atribute;
   }


   /** 
     * Descripción: Devuelve un tributo prefijado de uno extendido  
     * 
     * Parametros: ontologia principal prefijada  
    */ 
   private function AtributoPrefijado($atributo): string {
        $prefijado = "";
        $this->GetPrefijosOntologias();
        foreach($this->prefijosOntologias as $clave => $valor) {
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