<?php

namespace App\Service\Processor\Tool;

use PhpParser\Node\Expr\Empty_;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

/*
 * Descripción: Es la clase aparece como conjunto de utilidades para generar las ontologia que se seleccionan en
 *              El formulario de alineación (paso 3)
 *              Las ontologias principales, las que seleccionamos en el primer combo se listan del archivo assistant_mapping.xml
 *              Las campos (tributos de las ontologias ), se extraen del archivo EI2A.owl 
 *              Los atributos están en varios subniveles
 */              
class OntologiasAlineacionTool
{

     private $simplexml;
     private $simpleowl;
     private $prefijosOntologias;

     public function __construct(ContainerBagInterface $params){
          $sassistantMapping = $params->get('url_mapping_xml'); 
          $this->simplexml = simplexml_load_file($sassistantMapping);
          $ie2a = $params->get('url_ei2a_owl');  
          $this->simpleowl = simplexml_load_file($ie2a);
     }


   /** 
   * Descripción: Devuelve las ontologias principales del paso3
   */  
   public function GetPrefijosOntologias() {
      if (!isset($this->prefijosOntologias)) {
         $this->prefijosOntologias = $this->simplexml->getDocNamespaces();
         //si la clave tine base como nameespace se quita
         $this->prefijosOntologias = $this->change_key($this->prefijosOntologias,"","base");
      }     
   }


   /** 
     * Descripción: utilidad que cambia la clave de un registro en un array
     * 
     * Parámetros: 
     *        array : el array a tratar
     *        old_key: la clave a cambiar
     *        new _key: la nueva clave   
     */  
   function change_key($array, $old_key, $new_key ) {
      if( ! array_key_exists( $old_key, $array ) )
          return $array;
      $keys = array_keys( $array );
      $keys[ array_search( $old_key, $keys ) ] = $new_key;
      return array_combine( $keys, $array );
  }

   /** 
     * Descripción: Devuelve las ontologias principales del paso3
     */  
    public function GetOntologias():array {
         $Ontologiasview = array();
         foreach($this->simplexml->Entity as $Entry) {
            $type = $this->getAtributeXML($Entry,"rdf","type");
            $label = $this->getAtributeLabelComment($Entry,"rdfs");
            $Ontologiasview[$label] = $type;
         }             
         ksort($Ontologiasview);
         return $Ontologiasview;
     }

          
   /** 
     * Descripción: Devuelve laa subentidades definidas para una entidad en el mapping
     * 
     * Parámetros: ontologia principal  
     */ 
     public function GetSubEntidades($ontologia):array {
         $subentidades  = array();
         foreach($this->simplexml->Entity as $Entry) {
            $type = $this->getAtributeXML($Entry,"rdf","type");
            if ($type==$ontologia){
               if (count(iterator_to_array($Entry->Classification))) {
                  $classifications = $Entry->Classification;
                  foreach($classifications->Property as $property){
                    $key =  $this->getAtributeXML($property,"rdfs","label"); 
                    $subentidades[$key]=$key;
                  }
               }
               break;
            }
         } 
         return $subentidades;
     }

     
   /** 
     * Descripción: Devuelve los atributos de una ontologia principal
     *              Se rastrean los atributos de la ontologia  y todas sus subentidades de manera recursiva 
     * 
     * Parámetros: ontologia principal  
     */ 
    public function GetOntologia($ontologia):array {
      $nombreOntologia = $this->DameNombreOntologia($ontologia);
      $ontologiaExtendida = $this->ExtiendeEntidad($ontologia);
      $identificador = $this->DameIdentificadorOntologia($ontologia);
      //primero recogemos los tributos de la entidad principal
      $ontologiaPrincipal = $this->getAtributosEntidadHerencia($ontologia,$ontologiaExtendida,$nombreOntologia,array(),"","","");
      //ahora recogemos las entidades secundarias del mapa
      $subentides = array();
      foreach($this->simplexml->Entity as $Entry) {
         //si el lemento es la entidad selccionada
         if ($this->getAtributeXML($Entry,"rdf","type") == $ontologia) {
            //recojo las subentidades
            $arraySubentidades= array();
            $subentiadesEntarda = $this->DameSubEntidades($Entry,"","", $arraySubentidades,0);
            //preparo un array para tratar os datos
            foreach($subentiadesEntarda as $key=>$value) {
               $subentides[$key] = $value;
            }   
         }
      }
      //ordeno sin perder la estructura clave => valor
      asort($ontologiaPrincipal);
      //por cada una de las entidades secundarias recogemos los atributos igual que en la principal
      foreach($subentides as $keySecubdaria =>$valueSecundaria) { 
         if (strpos($keySecubdaria,"*")!==false){
            $ontologiaPrincipal[$keySecubdaria] = $valueSecundaria;
         } else {
             //preparo los parametros para pasar a la funcion que recoge los atributos
             //la cadena pude venir con ? como en el caso del cubo. 
             // ?se pone para separar el rango y distinguir entre los nodos que no varian por los namespaces 
             $tempArange = explode("?",$keySecubdaria);
             $range = "";
             if (count($tempArange)==2) {
               $range = $tempArange[1];
               $keySecubdaria = $tempArange[0];
             }
             //separo los nombre de las ontologias 
             $ontologias = explode(">",$keySecubdaria);
             $ontologia =  $ontologias[count($ontologias) -1];
             $nombreOntologias = explode("> ",$valueSecundaria);
             $nombreOntologia = $nombreOntologias[count($nombreOntologias) -1];
             
             //si la ontologia tiene rango se lo separo para tratar la ontologia
             //la función getAtributosEntidadHerencia se lo vuelve a juntar
            
             $ontologiaExtendida = $this->ExtiendeEntidad($ontologia);
 
             //ahora busco los atributos heredasdos
             //es decir la entidad puede hereda atributos de una superior
             $ontologiaPrincipal = $this->getAtributosEntidadHerencia($ontologia,
                                                                      $ontologiaExtendida,
                                                                      $nombreOntologia,
                                                                      $ontologiaPrincipal, 
                                                                      $keySecubdaria, 
                                                                      $valueSecundaria,
                                                                      $range);
       
          } 
      } 
      if ($identificador=="{CODIGO_ID}"){
         $this->array_unshift_assoc($ontologiaPrincipal,"COD_ID","Identificador entidad (requerido)");
      }

      foreach($ontologiaPrincipal as $key=>$value) { 
         if (strpos($key,$identificador)!==false) {
            $ontologiaPrincipal[$key] = $value . " (requerido)";
            break;
         }  
      }

      return $ontologiaPrincipal;
   }

   
   function array_unshift_assoc(&$arr, $key, $val)
   {
       $arr = array_reverse($arr, true);
       $arr[$key] = $val;
       $arr =  array_reverse($arr, true);
       return  $arr;
   }
   /** 
     * Descripción: Devuelve el array de atributos en formato clave valor de un nodo y sus padres en herecia
     * 
     * Parámetros: 
     *             ontologia:  nombre ontologia contraida con prefijo
     *             ontologiaExtendida: nombre ontologia extendida url completa
     *             nombreOntologia: Nombre de la ontologia para las etiquetas
     *             ontologiaJoin: array de la ontologia padre si el el padre es un array vacío
     *             keySecubdaria: la key de la ontologia secundaria para formar la cadena seguimiento 
     *             valueSecundaria: el valor la key de la ontologia secundaria para formar la cadena seguimiento    
   */ 

   public function getAtributosEntidadHerencia($ontologia, 
                                               $ontologiaExtendida, 
                                               $nombreOntologia, 
                                               $ontologiaJoin, 
                                               $keySecubdaria, 
                                               $valueSecundaria,
                                               $range): array {
      $ontologiaPrincipal = array();
      //quito el name space base
      $ontologiaExtendida = str_replace("http://opendata.aragon.es/def/ei2av2#","#", $ontologiaExtendida);
      //por cada una de las entidades 
      foreach($this->simpleowl->children('owl',true)->Class as $Entry) {
         //si conincide con la que busco
         if ($this->getAtributeAboutIdXML($Entry,"rdf") == $ontologiaExtendida) {
            //recojo sus entidades padre puede haber varias
            $ontologiasPadre = $this->DameEntidadesPadre($Entry);
            //por cada uno de  los padres
            foreach($ontologiasPadre as $ontologiaPadre) {
               //pongo la entidad en forma perfijada
               $ontologiaPadreComprimida = $this->ComprimeEntidad($ontologiaPadre);
               //recojo sus tributos (del padre)
               $ontologiaPrincipal = $this->getAtributesEntidad($ontologia, $nombreOntologia); 
               //por cada uno de los atributos del padre  
               foreach($this->simpleowl->children('owl',true)->Class as $Entry) {
                  //si son del padre que busco
                  if ($this->getAtributeAboutIdXML($Entry,"rdf") == $ontologiaPadre) 
                  {  //recojo os atributos
                     $ontologiasPrincipalPadre = $this->getAtributesEntidad($ontologiaPadreComprimida, $nombreOntologia); 
                     //selosm asigno ala ontologia
                     foreach($ontologiasPrincipalPadre as $key => $value) {
                        $ontologiaPrincipal[$key] = $value;
                     }   
                  }
               }
            }
            asort($ontologiaPrincipal);
         }
      }
      //ahora norlalizo los atributos del padre para que sean iguales que los del hijo
      //recordamos que el hijo pude tener ? y un rango y tener por delante %%%  para diferenciar de otros nodos hermanos
      $contadorkey =0;
      foreach($ontologiaPrincipal as $key=>$value) {
         $key1 = "";
         $keydistigue ="";
         if (empty($keySecubdaria)){
            $key1 = str_replace($ontologia,"", $keySecubdaria);
         }else {
            if (strpos($keySecubdaria,"%%%")){
               $tempos = strripos($keySecubdaria,"%%%");
               if ($tempos!=false){
                  $keydistigue = substr($keySecubdaria,0,$tempos+3);
                  $keySecubdaria = substr($keySecubdaria,$tempos+3);
               }
            } 
            if (strpos($keySecubdaria,$ontologia)===0) {
               $key1 = $keySecubdaria;
            }  else {
               $key1 = str_replace($ontologia,"", $keySecubdaria);
            }
            $key1 = $keydistigue . $key1;
         }
         //miro que la ultima propiedad no sea igual que la primera
         //por ejemplo en herencias de tipo igual que la 
         //para borrar y que quede correcto
         //entro pasa cuando la propiedad es del mimo tipo que su padre
         $temp = explode(">",$key1);
         $cuenta = count($temp) -1;
         if ($temp[$cuenta]==$ontologia) {
            $borra = ">" . $ontologia;
            $key1 = str_replace($borra,">", $key1);
         }

         $keyJoin = $key1 . $key; 
         $ValueJoin = str_replace($nombreOntologia,"", $valueSecundaria) . $value; 
         
         if (count($ontologiaJoin)>0) {
            while (array_key_exists($keyJoin,$ontologiaJoin)) {
               $keyJoin = $contadorkey . "%%%" . $keyJoin;
               $contadorkey++;
            }
         }
         if (!empty($range)){
            $keyJoin = $keyJoin . "?" . $range;
         }
         $ontologiaJoin[$keyJoin] = $ValueJoin;
      } 
      
      return $ontologiaJoin;
   }

    /** 
     * Descripción: Devuelve la descripcion y el enlace del la ontologia
     *
     * Parámetros: ontologia principal  
     */ 
   public function GetDescricionEnlaceOntologia($ontologia): array {
      $descripcion = null;
      $enalce = null;
      foreach($this->simplexml->Entity as $Entry) {
         if ($ontologia == $this->getAtributeXML($Entry,"rdf","type")) {
               $descripcion = [$this->getAtributeXML($Entry,"rdfs","comment")]; 
               $enalce = [$this->getAtributeAboutIdXML($Entry,"rdf")];  
            break;
         }
      }
      return [$descripcion,$enalce];
    }


      
   /** 
     * Descripción: Devuelve las subentidades de una ontologia se llama de manera recursiva
     * 
     *             entidad:  nombre ontologia contraida con prefijo
     *             atributePefijo: prefijo del atributo
     *             titulo: titulo que aprarece al usuario
     *             arraySubentidades: array de la subentidades
     *             nivel: nivel jerárquico de los nodos (nos interes para distinguir el primero)
     */ 
    private function DameSubEntidades($entidad, $atributePefijo, $titulo, $arraySubentidades, $nivel): array 
    {
      $nivel= $nivel+1;
     // $attribute =$this->getAtributeXML($entidad,"","attribute");
     //preparo el  label y la key (esto va los combos de slecion del formulario)
      $atributePefijo = empty($atributePefijo) ?  
                           $this->getAtributeXML($entidad,"rdf","type") : 
                                 $atributePefijo .">" . $this->getAtributeXML($entidad,"rdf","type"); 
      $titulo = empty($titulo) ? 
                  $this->getAtributeLabelComment($entidad,"rdfs") :
                         $titulo . " > " .$this->getAtributeLabelComment($entidad,"rdfs");
      $range = $this->getAtributeXML($entidad,"rdfs","range");
      $label = $this->getAtributeXML($entidad,"rdfs","label");
      //si tiene hijos (estrutira Entidad>Propiedad>Entidad>Propiedad)
      if ($entidad->Children()) { 
         //pudes se o propiedad o entidad
         if (count($entidad)>0) {
            if (count((array)$entidad->Property)>0)
            {
               $propiedades = $entidad->Property;
            } else {
               $propiedades = $entidad;
            }
         }
         //por cada de las popidades 
         foreach($propiedades as $propiedad) { 
               $type = $this->getAtributeXML($propiedad,"","type");
               //si es nivel 1 y la propdad es onlyfieldLink quere decir que son eas entidades 
               //que ponemos por enlace porque ya existen en una sola linea
               //se carga al principio
               if(($nivel == 1) && ($type=="onlyfieldLink")) {
                  $atributePefijo = $this->getAtributeXML($entidad,"rdf","type");
                  if ($type=="onlyfieldLink") {
                     $atribute = $this->getAtributeXML($propiedad,"","attribute");
                     $tituloPropiedad = $this->getNombreFieldLink($atribute);
                     $atributePefijo =  $this->getAtributeXML($propiedad,"rdf","label") . "%%%" . $atributePefijo . "*" . $atribute;
                     $key = $this->Damekey($atributePefijo,$arraySubentidades,$range,"");
                     $arraySubentidades[$key] =   $titulo . " > " . $tituloPropiedad . ' > '. $this->getAtributeXML($propiedad,"rdf","label");;
                  } 
               } else {
                  //sino pude ser 
                  if ($nivel == 1) {
                     //si el nive el 1 el prfijo simmprte comienza con el del principal
                     $atributePefijo = $this->getAtributeXML($entidad,"rdf","type");
                  }
                  //sila propida tiene atributos recojo para hacer el prefijo
                  if (count(((array)$propiedad->attributes()))) {
                     if (count($propiedad->attributes())==1) {
                        $atributePefijo =  $atributePefijo . "#" .  ((array)$propiedad->attributes()[0])[0];
                     } else  if (count($propiedad->attributes())==2) {
                        $atributePefijo =  $atributePefijo . "#" .  ((array)$propiedad->attributes()[1])[0];
                     }
                     //si la propiedad tiene una entidad llamo recursivamente
                     if ($propiedad->Entity){
                        $array = $this->DameSubEntidades($propiedad->Entity,$atributePefijo,$titulo, $arraySubentidades, $nivel);
                        foreach($array as $key=>$value)  {
                           //por cada uno cojo la key
                           $keyCreado = $this->Damekey($key,$arraySubentidades,$range,"");
                           $arraySubentidades[$keyCreado] = $value;
                        }
                     }
                  } 
               } 
            }    
       } else { 
         //si no tine hijos (ultimo nodo jerárquico) cojo la key
         $key = $this->Damekey($atributePefijo,$arraySubentidades,$range,$label);
         $arraySubentidades[$key] = $titulo;
       } 
       return $arraySubentidades;
    } 
   /** 
     * Descripción: Devuelve una key distinta de las existentes para poner insertar el elemento en el array
     *              si la key es igual el value se sobre escribe. Esta funcion devuelve una key distintia controlada
     * 
     *             key:  es la clave original
     *             arraySubentidades: es el array que inteto devolver como solucion
     *             range: si tiene rango también se trata por que la key cambia 
     */ 
    private function Damekey($key,$arraySubentidades,$range,$label): string  
    {
      $nuevaKey ="";
      if (!empty($range)){
         $key = $key . "?" . $range;
      }
      if (!empty($label) && (strpos($key,"qb:ComponentSpecification")!==false)){
         $key = $key. "&" . urlencode($label);
      }
      $keys = array_keys($arraySubentidades);
      if(array_search($key,$keys,true)===false) {
         $nuevaKey = $key;
      } else {
        $nuevaKey = array_search($key,$keys,true). '%%%' .$key;
      }
      return $nuevaKey;
    }

   /** 
     * Descripción: Loas ontologias Padre de la principal
     * 
     * Parámetros: ontologia principal prefijada  
     */ 
    private function DameEntidadesPadre($entidad): array 
    {
      $entidadesPadre = [];
      $count =0;                                          
      //$clasesPadre =  $this->getNodesXML($entidad,"subClassOf");
      foreach($entidad->Children("rdfs",true)->subClassOf as $node) {
         $clasePadre = "";
         foreach($node->Children('owl',true)->Class as $Entry) {
            $clasePadre =  $this->getAtributeAboutIdXML($Entry,"rdf");
         }
         if (empty($clasePadre)){
            $clasePadre =  $this->getAtributeXML($node,"rdf","resource");
         }
         if (!empty($clasePadre)) {
            $entidadesPadre[$count] = $clasePadre;
            $count++;
         }
      }    
      return $entidadesPadre;
    } 

   /** 
     * Descripción: Devuelve el nombre (label) de la ontologia 
     * 
     * Parámetros: ontologia principal prefijada  
     */ 
   private function DameNombreOntologia($ontologia): string 
   {
      $label = "";
      foreach($this->simplexml->Entity as $Entry) {
         if ($ontologia == $this->getAtributeXML($Entry,"rdf","type")) {
            $label = $this->getAtributeLabelComment($Entry,"rdfs");
            break;
         }
      }
      return $label;
   }

   /** 
     * Descripción: Devuelve el nombre (label) de la ontologia 
     * 
     * Parámetros: ontologia principal prefijada  
     */ 
    private function DameIdentificadorOntologia($ontologia): string 
    {
       $label = "";
       foreach($this->simplexml->Entity as $Entry) {
          if ($ontologia == $this->getAtributeXML($Entry,"rdf","type")) {
             $label = $this->getAtributeXML($Entry,"","attribute");
             break;
          }
       }
       return $label;
    }
 
   
   /** 
    * Descripción: Devuelve la entidad extendida por su prefijo
    * 
    * Parámetros: ontologia prefijada
    */ 
   private function ExtiendeEntidad($ontologia) : string {
      $extendida = "";
      $separador = explode(":",$ontologia);
      $this->GetPrefijosOntologias();
      if (count($separador) == 2){
         $entidad = $separador[1];
         if ($separador[0]!="base"){
            $prefijo = $this->prefijosOntologias[$separador[0]];
            $extendida = $prefijo . $entidad;
         } else {
            $extendida = $entidad;
         }
      } 
      return $extendida;
   }

   /** 
    * Descripción: Devuelve la entidad comprimida por su prefijo
    * 
    * Parámetros: ontologiaextendida : nombre de la entidad extendido
    */ 
    private function ComprimeEntidad($ontologiaextendida) : string {
      $comprimida = "";
      if (strpos($ontologiaextendida,"http")===false) {
         $comprimida="base:" .$ontologiaextendida;
     } else {
         $this->GetPrefijosOntologias();
         foreach($this->prefijosOntologias as $key=>$value) {
            if (strpos($ontologiaextendida,$value) !== false){
               $entidad = trim(str_replace($value,"",$ontologiaextendida));
               $comprimida= "{$key}:{$entidad}";
               break;
            }
         }
      }
      return $comprimida;
   }

   /** 
    * Descripción: Nos devuelve el array de dominios que pertenece un atributo
    *
    * Parámetros: atributo: el atributo a analizar
    */ 
   private function DameDominiosAtributo($atributo) : array {
      $dominios = array();
      $ClassP = $this->getNodesXML($atributo,'owl','Class');
      $unionOf = $this->getNodesXML($ClassP,'owl','unionOf');
      $Classs = $this->getNodesXML($unionOf,'owl','Class'); 
      foreach($Classs as  $Class){
         $Atributo = $this->getAtributeAboutIdXML($Class,"rdf");
         if (!in_array($Atributo,$dominios)) {
            array_push($dominios,$Atributo);
         }
      }   
      return $dominios;
   }

   /** 
    * Descripción: Nos indica si el atributo esta en el dominio de la entidad
    *
    * Parámetros: atributo: el atributo a analizar
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
   * Descripción: Devuelve el nombre del atributo de tipo fieldLink dado una url
   *             
   * Parámetros: propiedadPrefijada:: la url que corresponde al filldlink que busco  
   */ 
  private function getNombreFieldLink($propiedadPrefijada): string {
      $nombre = "";
      $propiedadExtendida = $this->ExtiendeEntidad($propiedadPrefijada);
      //pude estar aquí
      foreach($this->simpleowl->children('owl',true)->ObjectProperty as $ObjectProperty) {
         $atribute = $this->getAtributeXML($ObjectProperty,"rdf","about");
         $atribute = empty($atribute) ? $this->getAtributeXML($ObjectProperty,"rdf","ID") : $atribute;
         if ($atribute==$propiedadExtendida) { 
            $node = $ObjectProperty->Children('rdfs',true)->label;
            $nombre = ((array)iterator_to_array($node)['label'])[0];
            break;
         }      
      }
      //o aquí
      if (empty($nombre)){
         foreach($this->simpleowl->children('owl',true)->FunctionalProperty as $FunctionalProperty) {
            $atribute = $this->getAtributeXML($FunctionalProperty,"rdf","about");
            $atribute = empty($atribute) ? $this->getAtributeXML($FunctionalProperty,"rdf","ID") : $atribute;
            if ($atribute==$propiedadExtendida) { 
               $node = $FunctionalProperty->Children('rdfs',true)->label;
               $nombre = ((array)iterator_to_array($node)['label'])[0];
               break;
            }      
         }  
      }
      return $nombre;
   }  


  /** 
   * Descripción: Devuelve los atributos de una ontologia principal
   *             
   * Parámetros: Entidad entidad de la que voy a sacar lo atributos
   *             NombreEntidad: para formar el label
   */ 
   private function getAtributesEntidad($Entidad,$NombreEntidad): array {
      $atributos = array();
      foreach($this->simpleowl->children('owl',true)->DatatypeProperty as $DatatypeProperty) {
         if ($this->AtributoPerteneceEntidad($DatatypeProperty,$Entidad)) {
            $Atributo = $this->getAtributeAboutIdXML($DatatypeProperty,"rdf");
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
            $Atributo = $this->getAtributeAboutIdXML($FunctionalProperty,"rdf");
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
   * Parámetros: atributo: atributo extendido  
   *             entidad: entidad prefijada  
   *             separador: para distinguir el tipo ontologico    
   */ 
   private function DamePropiedad($atributo,$entidad,$separador): array {
      $AtributoPrefijado = $entidad . $separador .$this->AtributoPrefijado($atributo);
      $valor = "";
      switch ($separador) {
         case '#':
            foreach($this->simpleowl->Children('owl',true)->DatatypeProperty as $Entry) {
               if ($this->getAtributeAboutIdXML($Entry,"rdf") == $atributo) {
                  $node = $Entry->Children('rdfs',true)->label;
                  $valor = ((array)iterator_to_array($node)['label'])[0];
                  break;
               }
            }
            break;
         case '@':
               foreach($this->simpleowl->Children('owl',true)->FunctionalProperty as $Entry) {
                  if ($this->getAtributeAboutIdXML($Entry,"rdf") == $atributo) {
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
     * Descripción: Devuelve el atributo label o comment
     *             
     * 
     * Parámetros: 
     *                node: node donde se busca 
     *                pefix: prefijo 
     */ 
    private function getAtributeLabelComment($node, $prefix): string {
      $atribute = "";
      $atribute = $this->getAtributeXML($node,$prefix,"label");
      $atribute = empty($atribute) ? $this->getAtributeXML($node,$prefix,"comment"): $atribute;
      return $atribute;
   }

   /** 
     * Descripción: Devuelve el atributo about o ID de una ontologia principal
     *             
     * 
     * Parámetros: 
     *                node: node donde se busca 
     *                pefix: prefijo 
     */ 
    private function getAtributeAboutIdXML($node, $prefix ): string {
      $atribute = "";
      $atribute = $this->getAtributeXML($node,$prefix,"about");
      $atribute = empty($atribute) ? $this->getAtributeXML($node,$prefix,"ID"): $atribute;
      if (strpos($atribute,"http")===false) {
         $atribute = $this->prefijosOntologias["base"] . $atribute;
      }
      return $atribute;
   }


   /** 
    * Descripción: Devuelve los atributos de una ontologia principal
    *             
    * Parámetros: 
    *                node: node donde se busca 
    *                pefix: prefijo 
    *                name: nombre del atributo  
    */ 
   private function getAtributeXML($node, $pefix, $name ): string {
      $atribute = "";
      try{
          if (!empty($pefix)){
              if (in_array($name,array_keys((array)iterator_to_array($node->attributes($pefix,true))))) {
                  $atribute= ((array)iterator_to_array($node->attributes($pefix,true))[$name])[0];
              }
          } else {
              if (in_array($name,array_keys((array)iterator_to_array($node->attributes())))) {
                  $atribute= ((array)iterator_to_array($node->attributes())[$name])[0];
              }
          }

      } catch(\Exception $ex){
          $atribute = "";
      }
      return $atribute;
  }


   /** 
     * Descripción: Devuelve un tributo prefijado de uno extendido  
     * 
     * Parámetros: atributo: el atributo  
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
     * Parámetros: node: el nodo principal
     *             prefix: el prefijo que buscamos
     *             $name:  el tipo de nodo por su nombre
    */ 
   private function getNodesXML($node, $pefix, $name ) {
      $nodes = array();
      switch ($name) {
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