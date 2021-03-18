<?php

namespace App\Service\Controller; 
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use App\Entity\DescripcionDatos;
use App\Entity\OrigenDatos;
use App\Enum\TipoOrigenDatosEnum;
use App\Enum\RutasAyudaEnum;
use App\Enum\EstadoDescripcionDatosEnum;
use App\Enum\EstadoAltaDatosEnum;
use Proxies\__CG__\App\Entity\OrigenDatos as EntityOrigenDatos;

/*
 * Descripción: Es la clase aparece como conjunto de utilidades apara los controladores por estar en todos ellos y
 *              evitar su repetición  o por sacar el código de controlador y dejarlo  mas limpio y extendible.
 *              Hay funciones comunes y funciones solo para un controlador en especifico.
 */              
class ToolController
{

    private $urlAyuda = "";
    private $urlCrear = "";
    private $urlMenu = "";
    private $urlGeneratorInterface;

    /**
    * Descripción: Inyecto el manejador de url
    */
    public function __construct(UrlGeneratorInterface $urlGeneratorInterface)
    {
       $this->urlGeneratorInterface = $urlGeneratorInterface;
    }

    /***
     * Descripcion: devuelve el array de las url de los enlaces del encabezado
     *              para todos los controladores
     *              
     * Parametros:
     *             server:  objeto _SERVER del php del controlador
     *             paso:    paso del asistetente paso1, paso2, ect              
     */
    public function getAyudaCrearMenu($server,$paso,$usurioActual):array {
        if (empty($this->urlAyuda)){
            $this->urlAyuda = $this->urlGeneratorInterface->generate('asistentecamposdatos_ayuda_index',["pagina"=>$paso]);  
        }
        $pagina_actual = $this->getPaginaActual($server);
        $urlAyuda =  $this->urlAyuda ."?locationAnterior={$pagina_actual}";   

        if (empty($this->urlCrear)){
            $this->urlCrear =  $this->urlGeneratorInterface->generate("insert_asistentecamposdatos_paso1");
        }
        if ($this->DameUsuarioActual($usurioActual)[0]!= "MOCKSESSID"){
            if (empty($this->urlMenu)){
                $this->urlMenu =  $this->urlGeneratorInterface->generate("app_logout");
            }
        } else {
            $this->urlMenu = "";
        }
        return [$urlAyuda, 
                $this->urlCrear,
                $this->urlMenu];
    }

    /***
     * Descripcion: Devuelve el estado de la descripcion de los datos en formato para el listado
     *              Solo para controlador DescripcionDatosController.
     *                       
     * Parametros:
     *              estado: estado de la descripcion de los datos                   
     */
    public function DameEstadoDatos($estado) :array {

        $estadoKey = "";
        $estadoDescripcion= "";
        switch ($estado) {
             case EstadoDescripcionDatosEnum::BORRADOR:
                 $estadoDescripcion = "En borrador";
                 $estadoKey = "borrador";
                 break;
             case EstadoDescripcionDatosEnum::EN_ESPERA_PUBLICACION:
                 $estadoDescripcion = "En espera validación";
                 $estadoKey = "espera publicacion";
                 break;
             case EstadoDescripcionDatosEnum::EN_ESPERA_MODIFICACION:
                 $estadoDescripcion = "En espera validación";
                 $estadoKey = "espera modificacion";
                 break;
             case EstadoDescripcionDatosEnum::VALIDADO:
                 $estadoDescripcion = "Validado";
                 $estadoKey = "validado";
                 break;
             case EstadoDescripcionDatosEnum::DESECHADO:
                 $estadoDescripcion = "Desechado";
                 $estadoKey = "desechado";
                 break; 
             case EstadoDescripcionDatosEnum::EN_CORRECCION:
                 $estadoDescripcion = "En corrección";
                 $estadoKey = "correccion";
                 break;                  
             default:
                 $estadoDescripcion = "En borrador";
                 $estadoKey = "borrador";
                 break;
         }
         return[$estadoKey, $estadoDescripcion];
    }

    /***
     * Descripcion: Devuelve la url del botón "editar de la ficha.
     *              la url varia según donde lo dejo el asistente el usuario
     *              Solo para controlador DescripcionDatosController.
     *              
     * Parametros:
     *             data: objeto descripcion de los datos       
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
                case EstadoAltaDatosEnum::paso1:
                    $link = $this->urlGeneratorInterface->generate('update_asistentecamposdatos_paso1',["id"=>$data->getId()]);
                    break;
                case EstadoAltaDatosEnum::paso2:
                    $link = $this->urlGeneratorInterface->generate('update_asistentecamposdatos_paso2',["id"=>$data->getId()]);
                    break;
                case EstadoAltaDatosEnum::paso3:
                    $link = $this->urlGeneratorInterface->generate('update_asistentecamposdatos_paso3',["id"=>$data->getId()]);
                    break;
                case EstadoAltaDatosEnum::origen_database:
                    if (empty($linkCreaOrigendatos)){
                        $link = $this->urlGeneratorInterface->generate('update_asistentecamposdatos_database',["iddes"=>$data->getId(),"id"=>$origenDatos->getId()]);
                    } else {
                        $link = $linkCreaOrigendatos;
                    }
                    break;
                case EstadoAltaDatosEnum::origen_file:
                    if (empty($linkCreaOrigendatos)){
                        $link = $this->urlGeneratorInterface->generate('update_asistentecamposdatos_file',["iddes"=>$data->getId(),"id"=>$origenDatos->getId()]);
                    } else {
                        $link = $linkCreaOrigendatos;
                    }  
                    break;
                case EstadoAltaDatosEnum::origen_url:
                    if (empty($linkCreaOrigendatos)){
                        $link = $this->urlGeneratorInterface->generate('update_asistentecamposdatos_url',["iddes"=>$data->getId(), "id"=>$origenDatos['id']]);
                    } else {
                        $link = $linkCreaOrigendatos;
                    }  
                    break;
                case EstadoAltaDatosEnum::alineacion:
                    if (empty($linkCreaOrigendatos)){
                        $link = $this->urlGeneratorInterface->generate('insert_alineacion',["iddes"=>$data->getId(), "id"=>$origenDatos->getId(), "origen" =>  $origenDatos->getTipoOrigen()]);
                    } else {
                        $link = $linkCreaOrigendatos;
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
     * Descripcion: devuelve el usuario actual distinguiendo un entorno autenticado y sin autenticar
     *              para todos los controladores
     *              
     * Parametros:
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
     * Descripcion: Devuelve si se tiene permisopara ver una url sin tener en cuenta el estado
     *              para todos los controladores
     *              
     * Parametros:
     *             usurioActual:  usuario actual
     *             usuariodatos:  usuario de los datos           
     */
    public  function DamePermisoUsuarioActual($usurioActual, $usuariodatos) : string {
        [$usuario , $esAdminitrador ] = $this->DameUsuarioActual($usurioActual);
        $permisoEdicion = (($esAdminitrador) || ($usuario == $usuariodatos)) ? "block" : "none";
        return $permisoEdicion; 
    }

    /***
     * Descripcion: Devuelve si se tiene permisopara ver una url
     *              para todos los controladores
     *              
     * Parametros:
     *             usurioActual:  usuario actual
     *             usuariodatos:  usuario de los datos
     *             estado:        estado de los datos             
     */
    public  function DamePermisoUsuarioActualEstado($usurioActual, $usuariodatos, $estado) : string {

        [$usuario , $esAdminitrador ] = $this->DameUsuarioActual($usurioActual);
        $permisoEdicion = ($estado == EstadoDescripcionDatosEnum::BORRADOR  || $estado == EstadoDescripcionDatosEnum::EN_CORRECCION) ? "block" : "none";
        if ($permisoEdicion == "block")  {
             $permisoEdicion = (($esAdminitrador) || ($usuario == $usuariodatos)) ? "block" : "none";
        }
        return $permisoEdicion; 
    }

    /***
     * Descripcion: Devuelve el array con el estado de los botones de la ficha
     *              Solo para controlador DescripcionDatosController.
     *              
     * Parametros:
     *             esAdminitrador:  si el usuario es administrador
     *             estado:          el estado de la descripcion de los datos             
     */
    public function DameBotonesFicha($esAdminitrador, $estado) : array {

        $verbotonesModificacion = "none";
        $verbotonesPublicacion = "none";
        $verbotonesAdminValidar = "none";
        $verbotonesAdminDesechar = "none";
        $verbotonesAdminCorregir = "none";
        $verEditar = "none";

        if ($esAdminitrador) {
            if ($estado == EstadoDescripcionDatosEnum::EN_ESPERA_PUBLICACION ){
                $verbotonesAdminValidar = "block";
                $verbotonesAdminDesechar = "block";
                $verbotonesAdminCorregir = "block";
            } else if ($estado == EstadoDescripcionDatosEnum::EN_ESPERA_MODIFICACION) {
                $verbotonesAdminValidar = "block";
                $verbotonesAdminDesechar = "block";
                $verbotonesAdminCorregir = "none";
            }
        } else {
            if ( $estado == EstadoDescripcionDatosEnum::VALIDADO){
                $verbotonesModificacion = "block";
            }
            if ( $estado == EstadoDescripcionDatosEnum::BORRADOR ||  
                    $estado == EstadoDescripcionDatosEnum::EN_CORRECCION ){
                $verbotonesPublicacion = "block";
                $verEditar = "block";
            }
        }
        return [$verbotonesAdminValidar, $verbotonesAdminDesechar,$verbotonesAdminCorregir,
                $verbotonesModificacion, $verbotonesPublicacion,$verEditar];
    }

    /***
     * Descripcion: devuelve el array compuesto por
     *    campos:            conjunto de campos del origen delos datos
     *    ontologia:         ontologia principal asignada
     *    tableAlineacion:   tabla con los resultados de la alineación
     *    Solo para controlador DescripcionDatosController.
     * 
     *                      
     * Parametros:
     *             origenDatos:  objeto con el origen de datos          
     */
    public function getOntologiasFicha(OrigenDatos $origenDatos): array {

        $campos = !empty($origenDatos->getCampos()) ? explode(";",$origenDatos->getCampos()) : array();
        $ontologia =  (!empty($origenDatos->getAlineacionEntidad()))  ? $origenDatos->getAlineacionEntidad(): "";
        $tableAlineacion = (!empty($origenDatos->getAlineacionRelaciones()))  ? get_object_vars(json_decode(str_replace(",}","}",$origenDatos->getAlineacionRelaciones()))) : array();

        return [ $campos , $ontologia , $tableAlineacion];
    }

 
     /***
     * Descripcion: devuelve la url para volver al origen datos desde la pagina de alineación.
     *              Solo para controlador AlineacionDatosController.
     *              
     * Parametros:
     *             descripcionDatos: objeto con la descripción de los datos         
     */
    public function DameUrlAnteriorOrigendatos($origen, $id, $iddes, $server){

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
     * Descripcion: devuelve la url para ir al origen de datos desde el paso 1.3
     *              solo para controlador DescripcionDatosController
     *              
     * Parametros:
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
     * Descripcion: devuelve la url de soporte con el parametro locationAnterior ques la pagina de donde se 
     *              le llama.
     *              para todos los controladores
     *              
     * Parametros:
     *             server:  objeto _SERVER del php del controlador         
     */
    public function getSoporte($server){

        $urlSoporte = $this->urlGeneratorInterface->generate('asistentecamposdatos_ayuda_soporte');
        $actual_link = $this->getPaginaActual($server);
        $urlSoporte .= "?locationAnterior={$actual_link}";
        return $urlSoporte;
    }

    /***
     * Descripcion: devuelve la url de la pagina actual con las variables php
     *              
     * Parametros:
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



