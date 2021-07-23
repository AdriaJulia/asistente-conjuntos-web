<?php

namespace App\Service\Controller; 
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


use App\Service\Processor\Tool\OntologiasAlineacionTool;
use App\Entity\DescripcionDatos;
use App\Entity\OrigenDatos;
use App\Enum\TipoAlineacionEnum;
use App\Enum\TipoOrigenDatosEnum;
use App\Enum\RutasAyudaEnum;
use App\Enum\EstadoDescripcionDatosEnum;
use App\Enum\EstadoAltaDatosEnum;
use Proxies\__CG__\App\Entity\OrigenDatos as EntityOrigenDatos;

/*
 * Descripción: Es la clase aparece como conjunto de utilidades apara los controladores por estar en todos ellos y
 *              evitar su repetición  o por sacar el código de controlador y dejarlo  mas limpio y comprensible.
 *              Hay funciones comunes y funciones solo para un controlador en especifico.
 */              
class ToolController
{

    private $urlAyuda = "";
    private $urlSoporte = "";
    private $urlCrear = "";
    private $urlMenu = "";
    private $urlGeneratorInterface;
    private $ontologiasAlineacionTool;
    /**
    * Descripción: Inyecto el manejador de url
    */
    public function __construct(UrlGeneratorInterface $urlGeneratorInterface,
                                OntologiasAlineacionTool $ontologiasAlineacionTool)
                               
    {
       $this->urlGeneratorInterface = $urlGeneratorInterface;
       $this->ontologiasAlineacionTool = $ontologiasAlineacionTool;
    }

    /***
     * Descripción: devuelve el array de las url de los enlaces del encabezado
     *              para todos los controladores
     *              
     * Parámetros:
     *             server:  objeto _SERVER del php del controlador
     *             paso:    paso del asistetente paso1, paso2, ect              
     */
    public function getAyudaCrearMenu($server,$paso,$usurioActual):array {
        if (empty($this->urlAyuda)){
            $this->urlAyuda = $this->urlGeneratorInterface->generate('asistentecamposdatos_ayuda_index',["pagina"=>$paso]);  
        }

        if (empty($this->urlSoporte)){
            $this->urlSoporte = "/asistentecamposdatos/soporte";
        }

        if (empty($this->urlCrear)){
            $this->urlCrear =  $this->urlGeneratorInterface->generate("insert_asistentecamposdatos_paso0");
        }
        if ($this->DameUsuarioActual($usurioActual)[0]!= "MOCKSESSID"){
            if (empty($this->urlMenu)){
                $this->urlMenu =  $this->urlGeneratorInterface->generate("app_logout");
            }
        } else {
            $this->urlMenu = "";
        }

        return [$this->urlAyuda, 
                $this->urlSoporte, 
                $this->urlCrear,
                $this->urlMenu];
    }

    /***
     * Descripción: Devuelve la descripción del estado para el listado
     *              Solo para controlador DescripcionDatosController.
     *                       
     * Parámetros:
     *              estado: estado del conjunto datos                   
     */
    public function DameEstadoDatos($estado) :array {

        $estadoKey = "";
        $estadoDescripcion= "";
        switch ($estado) {
             case EstadoDescripcionDatosEnum::BORRADOR:
                 $estadoDescripcion = "Borrador";
                 $estadoKey = EstadoDescripcionDatosEnum::BORRADOR_KEY;
                 break;
             case EstadoDescripcionDatosEnum::EN_ESPERA_VALIDACION:
                 $estadoDescripcion = "En espera de validación";
                 $estadoKey = EstadoDescripcionDatosEnum::EN_ESPERA_VALIDACION_KEY;
                 break;
             case EstadoDescripcionDatosEnum::EN_ESPERA_MODIFICACION:
                 $estadoDescripcion = "Solicitud de modificación";
                 $estadoKey = EstadoDescripcionDatosEnum::EN_ESPERA_MODIFICACION_KEY;
                 break;
             case EstadoDescripcionDatosEnum::VALIDADO:
                 $estadoDescripcion = "Validado";
                 $estadoKey = EstadoDescripcionDatosEnum::VALIDADO_KEY;
                 break;
             case EstadoDescripcionDatosEnum::DESECHADO:
                 $estadoDescripcion = "Desechado";
                 $estadoKey = EstadoDescripcionDatosEnum::DESECHADO_KEY;
                 break; 
             case EstadoDescripcionDatosEnum::EN_CORRECCION:
                 $estadoDescripcion = "En corrección";
                 $estadoKey = EstadoDescripcionDatosEnum::EN_CORRECCION_KEY;
                 break;                  
             default:
                 $estadoDescripcion = "Borrador";
                 $estadoKey = EstadoDescripcionDatosEnum::BORRADOR_KEY;
                 break;
         }
         return[$estadoKey, $estadoDescripcion];
    }

    /***
     * Descripción: Devuelve la url del botón "editar" de la ficha.
     *              La url varia según donde lo dejo el asistente el usuario.
     *              Solo para controlador DescripcionDatosController.
     *              
     * Parámetros:
     *             data: objeto descripción de los datos       
     */
    public function DameEnlaceEdicion(DescripcionDatos $data) : array {
       
        $link = "";
        if (!empty($data->getOrigenDatos()) && $data->getOrigenDatos()->getId()!=null){
            $origenDatos = ($data->getOrigenDatos());      
        } else {
            $origenDatos = new EntityOrigenDatos();
            $linkCreaOrigendatos = $this->urlGeneratorInterface->generate('insert_asistentecamposdatos_url',["iddes"=>$data->getId()]);
        }
        
        if (($data->getEstado()==EstadoDescripcionDatosEnum::BORRADOR) || 
            ($data->getEstado()==EstadoDescripcionDatosEnum::EN_CORRECCION)) {
            switch ($data->getEstadoAlta()) {  
                case EstadoAltaDatosEnum::PASO1:
                    $link = $this->urlGeneratorInterface->generate('update_asistentecamposdatos_paso1',["id"=>$data->getId()]);
                    break;
                case EstadoAltaDatosEnum::PASO2:
                    $link = $this->urlGeneratorInterface->generate('update_asistentecamposdatos_paso2',["id"=>$data->getId()]);
                    break;
                case EstadoAltaDatosEnum::ORIGEN_DB:
                    if (empty($linkCreaOrigendatos)){
                        $link = $this->urlGeneratorInterface->generate('update_asistentecamposdatos_database',["iddes"=>$data->getId(),"id"=>$origenDatos->getId()]);
                    } else {
                        $link = $linkCreaOrigendatos;
                    }
                    break;
                case EstadoAltaDatosEnum::ORIGEN_FILE:
                    if (empty($linkCreaOrigendatos)){
                        $link = $this->urlGeneratorInterface->generate('update_asistentecamposdatos_file',["iddes"=>$data->getId(),"id"=>$origenDatos->getId()]);
                    } else {
                        $link = $linkCreaOrigendatos;
                    }  
                    break;
                case EstadoAltaDatosEnum::ORIGEN_URL:
                    if (empty($linkCreaOrigendatos)){
                        $link = $this->urlGeneratorInterface->generate('update_asistentecamposdatos_url',["iddes"=>$data->getId(), "id"=>$origenDatos->getId()]);
                    } else {
                        $link = $linkCreaOrigendatos;
                    }  
                    break;
                case EstadoAltaDatosEnum::ALINEACION:
                    $origenDatos = ($data->getOrigenDatos()); 
                    if ($origenDatos->getTipoAlineacion()==TipoAlineacionEnum::CAMPOS) {
                        $link = $this->urlGeneratorInterface->generate('insert_campos_alineacion',["iddes"=>$data->getId(), "id"=>$origenDatos->getId(), "origen" =>  $origenDatos->getTipoOrigen()]);
                    } else if ($origenDatos->getTipoAlineacion()==TipoAlineacionEnum::XML) {
                        $link = $this->urlGeneratorInterface->generate('insert_xml_alineacion',["iddes"=>$data->getId(), "id"=>$origenDatos->getId(), "origen" =>  $origenDatos->getTipoOrigen()]);
                    } else {
                        $link = $this->urlGeneratorInterface->generate('insert_campos_alineacion',["iddes"=>$data->getId(), "id"=>$origenDatos->getId(), "origen" =>  TipoOrigenDatosEnum::URL]);
                    }
                    break;                                                                                                                                  
                default:
                    $link = $this->urlGeneratorInterface->generate('update_asistentecamposdatos_paso1',["id"=>$data->getId()]);
                    break;
            }
        }
        return  [$origenDatos,$link] ;
    }

    /***
     * Descripción: devuelve el usuario actual distinguiendo un entorno autenticado y sin autenticar
     *              para todos los controladores
     *              
     * Parámetros:
     *             usurioActual:  usuario actual       
     */
    public function DameUsuarioActual($usurioActual): array{
        if (($usurioActual) && ($usurioActual != "MOCKSESSID") && getType($usurioActual)!="string") {
                $usuario =  $usurioActual->getExtraFields()['mail'];
                $esAdminitrador = ($usurioActual->getExtraFields()['roles'] == "ROLE_ADMIN");
        }else{
            $usuario =  "MOCKSESSID";
            $esAdminitrador = true;
        }
        return  [$usuario , $esAdminitrador ] ;
    }

     /***
     * Descripción: Devuelve si se tiene permiso para ver una url, sin tener en cuenta el estado
     *              para todos los controladores
     *              
     * Parámetros:
     *             usurioActual:  usuario actual
     *             usuariodatos:  usuario de los datos
     *             principal:       si es una distribución principal           
     */
    public  function DamePermisoUsuarioActual($usuariodatos,$usurioActual,$principal) : string {
        $permisoEdicion = "";
        if (!$principal) {
            $permisoEdicion = "none";
        } else {
            [$usuario , $esAdminitrador ] = $this->DameUsuarioActual($usurioActual);
            $permisoEdicion = (($esAdminitrador) || ($usuario == $usuariodatos)) ? "block" : "none";
            return $permisoEdicion; 
        }
    }

    /***
     * Descripción: Devuelve si se tiene permisopara ver una url
     *              para todos los controladores
     *              
     * Parámetros:
     *             usurioActual:  usuario actual
     *             usuariodatos:  usuario de los datos
     *             estado:        estado de los datos             
     */
    public  function DamePermisoUsuarioActualEstado( $usuariodatos, $usurioActual,$estado) : string {

        $permisoEdicion = "none";
        [$usuario , $esAdminitrador] = $this->DameUsuarioActual($usurioActual);
        if ($esAdminitrador) {
            $permisoEdicion = "block";
        } else {
            $permisoEdicion = ($estado == EstadoDescripcionDatosEnum::BORRADOR  || $estado == EstadoDescripcionDatosEnum::EN_CORRECCION) ? "block" : "none";
            if ($permisoEdicion == "block")  {
                 $permisoEdicion = (($usuario == $usuariodatos)) ? "block" : "none";
            }
        }
        return $permisoEdicion; 
    }

    /***
     * Descripción: Devuelve el array con el estado de los botones de la ficha
     *              Solo para controlador DescripcionDatosController.
     *              
     * Parámetros:
     *             esAdminitrador:  si el usuario es administrador
     *             estado:          el estado de la descripción de los datos             
     */
    public function DameBotonesFicha($esAdminitrador, $estado) : array {

        $verbotonesModificacion = "none";
        $verbotonesPublicacion = "none";
        $verbotonesAdminValidar = "none";
        $verbotonesAdminDesechar = "none";
        $verbotonesAdminCorregir = "none";
        $verbotonesAdminEditar = "none";
        $verEditar = "none";

        if ($esAdminitrador) {
            if ($estado == EstadoDescripcionDatosEnum::EN_ESPERA_VALIDACION ){
                $verbotonesAdminValidar = "block";
                $verbotonesAdminDesechar = "block";
                $verbotonesAdminCorregir = "block";
                $verbotonesAdminEditar = "block";
            } else if ($estado == EstadoDescripcionDatosEnum::EN_ESPERA_MODIFICACION) {
                $verbotonesAdminValidar = "block";
                $verbotonesAdminDesechar = "block";
            } else if ($estado == EstadoDescripcionDatosEnum::EN_CORRECCION ) {
                $verEditar = "block";
            } else if ($estado == EstadoDescripcionDatosEnum::VALIDADO){
                $verbotonesAdminEditar = "block";
            }
        } else {
            if ( $estado == EstadoDescripcionDatosEnum::VALIDADO){
                $verbotonesAdminEditar = "block";
            }
            if ( $estado == EstadoDescripcionDatosEnum::BORRADOR ||  
                 $estado == EstadoDescripcionDatosEnum::EN_CORRECCION ){
                $verEditar = "block";
            }
            if ( $estado == EstadoDescripcionDatosEnum::EN_CORRECCION ){
               $verbotonesPublicacion = "block";
            }
        }
        return [$verbotonesAdminValidar, $verbotonesAdminDesechar,$verbotonesAdminCorregir,
                $verbotonesModificacion, $verbotonesPublicacion,$verbotonesAdminEditar,$verEditar];
    }

    /***
     * Descripción: devuelve el array compuesto por
     *    campos:            conjunto de campos del origen delos datos
     *    ontologia:         ontologia principal asignada
     *    tablaAlineacion:   tabla con los resultados de la alineación
     * 
     *    Solo para controlador DescripcionDatosController.
     *                      
     * Parámetros:
     *             origenDatos:  objeto con el origen de datos          
     */
    public function getOntologiasFicha(OrigenDatos $origenDatos): array {

        $campos = !empty($origenDatos->getCampos()) ? explode(";",$origenDatos->getCampos()) : array();
        $ontologia = "";
        foreach($this->ontologiasAlineacionTool->GetOntologias() as $key =>$value){
           if ($origenDatos->getAlineacionEntidad() == $value) {
            $ontologia = $key;
           }
        }
        $temp = (!empty($origenDatos->getAlineacionRelaciones()))  ? get_object_vars(json_decode(str_replace(",}","}",$origenDatos->getAlineacionRelaciones()))) : array();
        $tablaAlineacion = [];
        foreach($temp as $key => $value){
            $temp2 =  explode("&&&",$value);
            $value = end($temp2);
            array_push($tablaAlineacion, array("key"=>$key,"value"=>$value));
        }

        return [ $campos , $ontologia , $tablaAlineacion];
    }

    /***
     * Descripción: comprueba que los campos nuevos estén en los alineados antiguos
     *    camposActuales:    array conjunto de campos del origen delos datos
     *    camposDistintos:   falg si hay cambios
     *    camposAlineados:   array de los alienados
                    
     * Parámetros:
     *             campos:  cadena con los campos
     *             camposAlineados:   array con los campos alineados    
     */
    public function getOntologiasAlienedas($campos,$alineaciones): array {

        $camposDistintos = false;
           //tomo los campos Actuales
        $camposActuales =  !empty($campos) ? explode(";",$campos) : array();
        $camposAlineados = array_keys($alineaciones); 
        if (count($camposActuales) && count($camposAlineados) ) { 
            foreach($camposAlineados as $camposAlineado){
                if (array_search($camposAlineado,$camposActuales)===false) {
                    $camposDistintos = true;
                    break;
                }
            }
        }
        return [$camposActuales , $camposDistintos, $camposAlineados];
    }


    /***
     * Descripción: devuelve una alineación-relaciones valida respecto a los campos y la alineación-relaciones actual 
     *    nuevaAlineacion:  string con la nueva alineación
     *         
     * Parámetros:
     *             campos:  cadena con los campos
     *             camposAlineados:   array con los campos alineados    
     */
    public function getNuevaAlineacion($campos,$alineaciones): string {

        $nuevaAlineacion = "{";
           //tomo los campos Actuales
        $camposActuales =  !empty($campos) ? explode(";",$campos) : array();
        $camposAlineadosKeys = array_keys($alineaciones); 
        if (count($camposActuales) && count($camposAlineadosKeys) ) {
            foreach($camposAlineadosKeys as $camposAlineadokey){
                if (array_search($camposAlineadokey,$camposActuales)!==false) {
                    $alinecion = "\"{$camposAlineadokey}\":\"{$alineaciones[$camposAlineadokey]}\",";
                    $nuevaAlineacion .= $alinecion;
                }
            }
            $nuevaAlineacion = (strlen($nuevaAlineacion)>1) ? substr($nuevaAlineacion,0,-1) : $nuevaAlineacion;
        }
        $nuevaAlineacion .= "}";
        return $nuevaAlineacion;
    }


     /***
     * Descripción: devuelve la url para volver al origen datos desde la pagina de alineación.
     *              Solo para controlador AlineacionDatosController.
     *              
     * Parámetros:
     *             origen: tipo de origen del origen de los datos  
     *             id: identificador del origen de los datos 
     *             iddes: identificador de la distribución      
     */
    public function DameUrlAnteriorOrigendatos($origen, $id, $iddes){

        switch ($origen) {
            case 'url':
             $locationAnterior = $this->urlGeneratorInterface->generate('update_asistentecamposdatos_url',["id"=>$id, "iddes"=>$iddes]);
                break;
            case 'file':
             $locationAnterior = $this->urlGeneratorInterface->generate('update_asistentecamposdatos_file',["id"=>$id, "iddes"=>$iddes]);
                 break;  
            case 'database':
             $locationAnterior = $this->urlGeneratorInterface->generate('update_asistentecamposdatos_database',["id"=>$id, "iddes"=>$iddes]);
                 break;          
         }
         return $locationAnterior;
    }
 
 
    /***
     * Descripción: devuelve la url para ir al origen de datos desde el paso 3
     *              solo para controlador DescripcionDatosController
     *              
     * Parámetros:
     *             descripcionDatos: objeto con la descripción de los datos         
     */
    public function DameSiguienteOrigendatos(DescripcionDatos $descripcionDatos){
        $locationSiguiente = "";
        $id = $descripcionDatos->getId();
        if ($descripcionDatos->TieneOrigenDatos()) {
            $iddes = $descripcionDatos->getOrigenDatos()->getId();
            if ($descripcionDatos->getOrigenDatos()->getTipoOrigen() == TipoOrigenDatosEnum::BASEDATOS) {
                $locationSiguiente =  $this->urlGeneratorInterface->generate('update_asistentecamposdatos_database',["iddes"=>$id, "id"=>$iddes ]);
            } elseif ($descripcionDatos->getOrigenDatos()->getTipoOrigen() == TipoOrigenDatosEnum::ARCHIVO)  {
                $locationSiguiente =  $this->urlGeneratorInterface->generate('update_asistentecamposdatos_file',["iddes"=>$id, "id"=>$iddes ]);
            } else {
                $locationSiguiente =  $this->urlGeneratorInterface->generate('update_asistentecamposdatos_url',["iddes"=>$id, "id"=>$iddes]);
            }
        } else {
            $locationSiguiente =  $this->urlGeneratorInterface->generate('insert_asistentecamposdatos_url',["iddes"=>$id]);
        }  
        return $locationSiguiente;
    }


     /***
     * Descripción: devuelve la url para ir al origen de datos desde el paso 4
     *              solo para controlador DescripcionDatosController
     *              
     * Parámetros:
     *             descripcionDatos: objeto con la descripción de los datos  
     *             id: identificador del origen de los datos 
     *             iddes: identificador de la distribución
     *             origen: tipo de origen del origen de los datos  
     *                    
     */
    public function DameSiguienteAlineacion($tipoAlineacionEnum ,$iddes, $id, $origen){
        $locationSiguiente = "";

        if ($tipoAlineacionEnum == TipoAlineacionEnum::XML)  {
            $locationSiguiente =  $this->urlGeneratorInterface->generate('insert_xml_alineacion',["iddes"=>$iddes, "id"=>$id,"origen"=>$origen]);
        } else {
            $locationSiguiente =  $this->urlGeneratorInterface->generate('insert_campos_alineacion',["iddes"=>$iddes, "id"=>$id,"origen"=>$origen ]);
        }
        return $locationSiguiente;
    }

    /***
     * Descripción: devuelve la url de la pagina actual con las variables php
     *              
     * Parámetros:
     *             server:  objeto _SERVER del php del controlador         
     */
    private function getPaginaActual($server) {
        $actual_link = "";  
        if (array_key_exists("HTTP_HOST",$server)) {
            $httpHost = "$server[HTTP_HOST]";
            $actual_link = (isset($server['HTTPS']) && $server['HTTPS'] === 'on' ? "https" : "http") . "://$httpHost$server[REQUEST_URI]";
        }
        return $actual_link;
    }
}



