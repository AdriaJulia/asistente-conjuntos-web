<?php

namespace App\Controller;


use App\Enum\RutasAyudaEnum;
use App\Enum\TipoOrigenDatosEnum;
use App\Enum\EstadoAltaDatosEnum;

use App\Service\Controller\ToolController;
use App\Service\Manager\DescripcionDatosManager;
use App\Service\Processor\DescripcionDatosPaso1FormProcessor;
use App\Service\Processor\DescripcionDatosPaso2FormProcessor;
use App\Service\Processor\DescripcionDatosPaso3FormProcessor;
use App\Service\Processor\DescripcionDatosWorkFlowFormProcessor;
use App\Service\CurrentUser;
use App\Service\Manager\OrigenDatosManager;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Ldap\Security\LdapUser;
use Psr\Log\LoggerInterface;


/*
 * Descripción: Es el controlador de todas la llamadas del paso 1 (1.1, 1.2 y 1.3)
 *              para crear o actualizar la descripción de los datos.
 *              También controla la ficha del conjunto de datos y el listado.
 */
class DescripcionDatosController extends AbstractController
{

     private $ClassBody = "";
     private $urlAyuda = "";
     private $urlCrear = "";
     private $urlMenu = "";

    /***
     * Descripcion: Accion de la llamada al listado de los datos
     * Parametros:
     *             pagina:                    para el paginado de los datos
     *             tamano:                    tamaño de la pagina para el paginado de los datos
     *             descripcionDatosManager :  repositorio de la descripcion de datos
     *             toolController:            clase de herramientas para procesoso comunes de los controladores
     *             request:                   El objeto request de la llamada
     */
     /**
     * @Route("/asistentecamposdatos", requirements={"pagina"="\d+","tamano"="\d+" }, name="asistentecamposdatos_index")
     */
    public function IndexAction(int $pagina=1,
                                int $tamano=0,
                                DescripcionDatosManager $descripcionDatosManager,
                                LoggerInterface $logger,
                                ToolController $toolController,
                                Request $request) {
        //el class de body en este controlador no es siempre el mismo       
        $this->ClassBody = "listado comunidad usuarioConectado";
        //tomo las urls del menu superior 
        [$this->urlAyuda, $this->urlCrear, $this->urlMenu]  = $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::LISTADO_GENERAL,$this->getUser());
        //tomo el listado
        $descripcionDatos = $descripcionDatosManager->get($pagina, $tamano, $request->getSession());
        $datosGrid = array();
        if ($descripcionDatos!= array()){
            if (count($descripcionDatos['data'])>0) {
                //por cada uno de loe elementos del listado 
                foreach($descripcionDatos['data'] as $data) {
                    //recojo el estado, el enlace a la ficha, etc..
                    [$estadoKey, $estadoDescripcion] = $toolController->DameEstadoDatos($data['estado']);
                    $actual_link = $this->generateUrl('asistentecamposdatos_id',["id"=>$data['id']]);
                    $inicio = (new DateTime($data['creadoEl']))->format('Y-m-d');
                    $fin =  (new DateTime($data['actualizadoEn']))->format('Y-m-d'); 
                    //relleno el array odt que se va a mostrar
                    $datosGrid[] = array("estadoKey"=>$estadoKey,  
                                         "estadoDescripcion"=> $estadoDescripcion,
                                         "link" =>  $actual_link, 
                                         "descripcion" =>  $data['denominacion'],
                                         "fechaInicio" => $inicio, 
                                         "fechaFin"=>  $fin);
                }
            } 
        } else {
            $descripcionDatos = array('totalElementos'=>0);
        }

        return $this->render('grid.html.twig',['ClassBody' => $this->ClassBody,
                                               'urlCrear' =>  $this->urlCrear,
                                               'urlAyuda' =>  $this->urlAyuda,
                                               'urlMenu' =>  $this->urlMenu,
                                               'descripcionDatos'=>  $datosGrid,
                                               "totalElementos"=>  $descripcionDatos['totalElementos']
                                              ]);
    }

    /***
     * Descripcion: Accion que muestra la ficha del conjunto de datos
     * Parametros:
     *             id:                        id de la descripcion de los datos que se la va a insertar el origen
     *             descripcionDatosManager:   repositorio de la descripcion de datos
     *             origenDatosManager :       repositorio del origen de datos
     *             toolController:            clase de herramientas para procesoso comunes de los controladores
     *             request:                   El objeto request de la llamada
     */
     /**
     * @Route("/asistentecamposdatos/{id}", requirements={"id"="\d+"}, name="asistentecamposdatos_id")
     */
    public function GetAction(int $id,
                              DescripcionDatosManager $descripcionDatosManager,
                              OrigenDatosManager $origendatosManager,
                              LoggerInterface $logger,
                              ToolController $toolController,
                              Request $request) {
        //inicializo para la plantilla twig 
        $campos = "";
        $filas = "";
        $camposDistintos = false;
        $muestraError = false;
        $camposActual =""; 
 
        $errorProceso = "";
        $urlworkflow = "";
        $editLink= "";
        $datos = array();
        $ontologia = "";
        $tableAlineacion = array();
        $tabla = array();
        $origenDatos  = null;
 
        $verbotonesAdminValida = null;
        $verbotonesAdminDesechar= null;;
        $verbotonesAdminCorregir= null;
        $verbotonesModificacion= null;
        $verbotonesPublicacion= null;
        $verEditar= null;

        //el class de body en este controlador no es siempre el mismo    
        $this->ClassBody = "fichaRecurso comunidad usuarioConectado";  
        //tomo las urls del menu superior 
        [$this->urlAyuda, $this->urlCrear, $this->urlMenu]  =  $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::FICHA_GENERAL,$this->getUser());
        //recojo el objeto descripcion datos
        $data = $descripcionDatosManager->find($id, $request->getSession());
        // solo se puede acceder si el usuario es el mismo que lo creó
        $permisoEdicion = $toolController->DamePermisoUsuarioActual($data->getUsuario(), $this->getUser());

        if ($permisoEdicion!== "none") {
            $tabla = null;
            //recojo los valores de los campos del cojunto de datos formateados para la ficha 
            $datos  = $data->getToView($toolController);
            //recojo el enlace de boton "editar", que depende del últiomo estado donde se quedó el usuario en el asistente
            [$origenDatos, $editLink] = $toolController->DameEnlaceEdicion($data);
            //recojo la url contra la que se va alanzar cualquiera de las acciones con los botones de la ficha (validar, solicitar, etc...)
            $urlworkflow = $this->generateUrl('asistentecamposdatos_workflow',["id"=>$data->getId()]);
            //inicializo para la plantilla twig 
            $tabla = array("campos" =>array(), "filas"=>0);
            $tableAlineacion = array();
            //recojo la informacion del paso 3 (la ontologia - entidad principal) y la lista de campos alineados
            $ontologia =  "";
            if ($origenDatos->getId()!=null) {
                [$campos , $ontologia , $tableAlineacion] = $toolController->getOntologiasFicha($data->getOrigenDatos());
                [$filas, $camposActuales, $errorProceso] = $origendatosManager->DatosFicha($data->getOrigenDatos()->getId(),$request->getSession());
                $camposActual =  !empty($camposActuales) ? explode(";",$camposActuales) : array();
                $camposDistintos = ($campos != $camposActual); 
                $tabla = array("campos" => $campos, "filas"=>$filas);
                $muestraError = $camposDistintos  || !empty($errorProceso); 
            }

           //recojo si el usuario es administrador para mostrar botones de su perfil o de perfil usuario (no administrador)
            [$usuario, $esAdminitrador] = $toolController->DameUsuarioActual($this->getUser());
            // recojo los fags  de los botones para mostrarlos o no
            [$verbotonesAdminValida, 
             $verbotonesAdminDesechar,
             $verbotonesAdminCorregir,
             $verbotonesModificacion, 
             $verbotonesPublicacion,
             $verEditar] = $toolController->DameBotonesFicha($esAdminitrador,
                                                             $data->getEstado());
        }
       
        
        return $this->render('descripcion/ficha.html.twig',['ClassBody' => $this->ClassBody,
                                                            'permisoEdicion' => $permisoEdicion,
                                                            'urlCrear' =>  $this->urlCrear,
                                                            'urlAyuda' =>  $this->urlAyuda,
                                                            'urlMenu' =>  $this->urlMenu,
                                                            'camposDistintos' => $camposDistintos,
                                                            'errorProceso' => $errorProceso,
                                                            'camposAprobados' => $campos,
                                                            'camposActuales' => $camposActual,
                                                            'muestraError' => $muestraError,
                                                            'urlworkflow' => $urlworkflow,
                                                            'verbotonesModificacion' => $verbotonesModificacion,
                                                            'verbotonesPublicacion' => $verbotonesPublicacion,
                                                            'verbotonesAdminValidar' => $verbotonesAdminValida,
                                                            'verbotonesAdminDesechar' => $verbotonesAdminDesechar,
                                                            'verbotonesAdminCorregir' => $verbotonesAdminCorregir,
                                                            'editLink' => $editLink,
                                                            'verEditar' => $verEditar,
                                                            'ontologia' => $ontologia,
                                                            'tableAlineacion' => $tableAlineacion,
                                                            'data'=>$datos,
                                                            'table'=>$tabla]);
    }

    /***
     * Descripcion: Action de la solicitud  de un cambio de estado, al pulsar un botón de la ficha del conjunto de datos 
     *              Es el action del popup donde se solicita el mensaje para el cambio de estado.
     *              Este proceso envía el correo electrónico en la parte de Apirest
     * Parametros:
     *             id:                                      id de la descripcion de los datos
     *             descripcionDatosWorkFlowFormProcessor:   objeto que realiza el proceso back de la solicitud
     *             descripcionDatosManager:                 repositorio de la descripcion de datos
     *             origenDatosManager :                     repositorio del origen de datos
     *             request:                                 El objeto request de la llamada
     */
    /**     
     * @Route("/asistentecamposdatos/workflow/{id}", requirements={"id"="\d+"}, name="asistentecamposdatos_workflow")
     */
    public function InsertWorkflowAction($id,
                                         DescripcionDatosWorkFlowFormProcessor $descripcionDatosWorkFlowFormProcessor,
                                         DescripcionDatosManager $descripcionDatosManager,
                                         LoggerInterface $logger,
                                         Request $request) {
        //se toma el objeto por id sde la BD con su estado actual                                           
        $descripcionDatos = $descripcionDatosManager->find($id,$request->getSession());
        //se procesa el cambio de estado
        [$form] = ($descripcionDatosWorkFlowFormProcessor)($descripcionDatos, $request);
        if ($form->isSubmitted() && $form->isValid()) {
            //  $this->addFlash('success', 'It sent!'); ; 
            $response = new \Symfony\Component\HttpFoundation\Response(
                'ok actualizado',
                \Symfony\Component\HttpFoundation\Response::HTTP_OK,
                ['content-type' => 'text/html']
            );
        } else {
            $response = new \Symfony\Component\HttpFoundation\Response(
                'ko no actualizado',
                \Symfony\Component\HttpFoundation\Response::HTTP_EXPECTATION_FAILED,
                ['content-type' => 'text/html']
            );
        }
        return $response;
     }

    /***
     * Descripcion: Inserta una descripción de datos en el formulario 1.1
     * 
     * Parametros:
     *             descripcionDatosFormProcessor:   objeto que realiza el proceso back de la solicitud
     *             descripcionDatosManager:         repositorio de la descripcion de datos
     *             origenDatosManager :             repositorio del origen de datos
     *             request:                         el objeto request de la llamada
     */

    /**
     * @Route("/asistentecamposdatos/paso1", name="insert_asistentecamposdatos_paso1")
     */
    public function InsertPaso1Action(DescripcionDatosPaso1FormProcessor $descripcionDatosFormProcessor,
                                      DescripcionDatosManager $descripcionDatosManager,
                                      LoggerInterface $logger,
                                      ToolController $toolController,
                                      Request $request) {
                    
        //el class de body en este controlador no es siempre el mismo    
        $this->ClassBody = "asistente comunidad usuarioConectado";
        //tomo las urls del menu superior  
        [$this->urlAyuda, $this->urlCrear, $this->urlMenu]  =  $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::DESCRIPCION_PASO11,$this->getUser());

        $descripcionDatos = $descripcionDatosManager->new();
        [$form,$descripcion] = ($descripcionDatosFormProcessor)($descripcionDatos, $request);
        if ($form->isSubmitted() && $form->isValid()) {
            //  $this->addFlash('success', 'It sent!'); ; 
            return $this->redirectToRoute('update_asistentecamposdatos_paso2',["id"=>$descripcion->getId()]); 
        } else {
            return $this->render('descripcion/paso1.html.twig', [
                'ClassBody' => $this->ClassBody,
                'urlCrear' =>  $this->urlCrear,
                'urlAyuda' =>  $this->urlAyuda,
                'urlMenu' =>  $this->urlMenu,
                'permisoEdicion' => "block",
                'paso1_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }
             
    /***
     * Descripcion: Actualiza una descripción de datos en el formulario 1.1
     * 
     * Parametros:
     *             id:                              id de la descripcion de los datos
     *             descripcionDatosFormProcessor:   objeto que realiza el proceso back de la solicitud
     *             descripcionDatosManager:         repositorio de la descripcion de datos
     *             origenDatosManager :             repositorio del origen de datos
     *             request:                         el objeto request de la llamada
     */
    /**
     * @Route("/asistentecamposdatos/paso1/{id}", requirements={"id"="\d+"}, name="update_asistentecamposdatos_paso1")
     */
    public function UpdatePaso1Action(int $id,
                                      DescripcionDatosPaso1FormProcessor $descripcionDatosFormProcessor,
                                      DescripcionDatosManager $descripcionDatosManager,
                                      LoggerInterface $logger,
                                      ToolController $toolController,
                                      Request $request) {
            

         //el class de body en este controlador no es siempre el mismo    
        $this->ClassBody = "asistente comunidad usuarioConectado"; 
        //tomo las urls del menu superior 
        [$this->urlAyuda, $this->urlCrear, $this->urlMenu]  = $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::DESCRIPCION_PASO11,$this->getUser());

        $descripcionDatos = $descripcionDatosManager->find($id, $request->getSession());
        // solo se puede acceder si el estado es correcto y el usuario es el mismo que lo creó
        $permisoEdicion = $toolController->DamePermisoUsuarioActualEstado($descripcionDatos->getUsuario(), 
                                                                          $this->getUser(),
                                                                          $descripcionDatos->getEstado());
        [$form] = ($descripcionDatosFormProcessor)($descripcionDatos, $request);
        if ($form->isSubmitted() && $form->isValid()) {
            //   $this->addFlash('success', 'Descripción actualizada'); 
            return $this->redirectToRoute('update_asistentecamposdatos_paso2',["id"=>$id]); 
        } else {
            return $this->render('descripcion/paso1.html.twig', [
                'ClassBody' => $this->ClassBody,
                'urlCrear' =>  $this->urlCrear,
                'urlAyuda' =>  $this->urlAyuda,
                'urlMenu' =>  $this->urlMenu,
                'permisoEdicion' => $permisoEdicion,
                'paso1_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }

    /***
     * Descripcion: Actualiza una descripción de datos en el formulario 1.2
     * 
     * Parametros:
     *             id:                              id de la descripcion de los datos
     *             descripcionDatosFormProcessor:   objeto que realiza el proceso back de la solicitud
     *             descripcionDatosManager:         repositorio de la descripcion de datos
     *             origenDatosManager :             repositorio del origen de datos
     *             request:                         el objeto request de la llamada
     */
    /**
     * @Route("/asistentecamposdatos/paso2/{id}", requirements={"id"="\d+"}, name="update_asistentecamposdatos_paso2")
     */
    public function UpdatePaso2Action(int $id,
                                      DescripcionDatosPaso2FormProcessor $descripcionDatosFormProcessor,
                                      DescripcionDatosManager $descripcionDatosManager,
                                      LoggerInterface $logger,
                                      ToolController $toolController,
                                      Request $request) {

        //el class de body en este controlador no es siempre el mismo    
        $this->ClassBody = "asistente comunidad usuarioConectado"; 
        //tomo las urls del menu superior 
        [$this->urlAyuda, $this->urlCrear, $this->urlMenu]  =  $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::DESCRIPCION_PASO12,$this->getUser());

        $descripcionDatos = $descripcionDatosManager->find($id, $request->getSession());
        // solo se puede acceder si el estado es correcto y el usuario es el mismo que lo creó
        $permisoEdicion = $toolController->DamePermisoUsuarioActualEstado($descripcionDatos->getUsuario(), 
                                                                          $this->getUser(),
                                                                          $descripcionDatos->getEstado());
        [$form] = ($descripcionDatosFormProcessor)($descripcionDatos, $request);

        [$form] = ($descripcionDatosFormProcessor)($descripcionDatos, $request);
        if ($form->isSubmitted() && $form->isValid()) {
           // $this->addFlash('success', 'It sent!'); 
            return $this->redirectToRoute('update_asistentecamposdatos_paso3',["id"=>$id]); 
        } else {
            $locationAnterior = $this->generateUrl('update_asistentecamposdatos_paso1',["id"=>$id]);
            return $this->render('descripcion/paso2.html.twig', [
                'locationAnterior' => $locationAnterior,
                'ClassBody' => $this->ClassBody,
                'urlCrear' =>  $this->urlCrear,
                'urlAyuda' =>  $this->urlAyuda,
                'urlMenu' =>  $this->urlMenu,
                'permisoEdicion' => $permisoEdicion,
                'paso2_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }

    /***
     * Descripcion: Actualiza una descripción de datos en el formulario 1.3
     * 
     * Parametros:
     *             id:                              id de la descripcion de los datos
     *             descripcionDatosFormProcessor:   objeto que realiza el proceso back de la solicitud
     *             descripcionDatosManager:         repositorio de la descripcion de datos
     *             origenDatosManager :             repositorio del origen de datos
     *             request:                         el objeto request de la llamada
     */
    /**
     * @Route("/asistentecamposdatos/paso3/{id}", requirements={"id"="\d+"}, name="update_asistentecamposdatos_paso3")
     */
    public function UpdatePaso3Action(int $id,
                                      DescripcionDatosPaso3FormProcessor $descripcionDatosFormProcessor,
                                      DescripcionDatosManager $descripcionDatosManager,
                                      LoggerInterface $logger,
                                      ToolController $toolController,
                                      Request $request) {

        //el class de body en este controlador no es siempre el mismo    
        $this->ClassBody = "asistente comunidad usuarioConectado"; 
        //tomo las urls del menu superior 
        [$this->urlAyuda, $this->urlCrear, $this->urlMenu]  =  $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::DESCRIPCION_PASO13,$this->getUser());

        $descripcionDatos = $descripcionDatosManager->find($id, $request->getSession());
        // solo se puede acceder si el estado es correcto y el usuario es el mismo que lo creó
        $permisoEdicion = $toolController->DamePermisoUsuarioActualEstado($descripcionDatos->getUsuario(), 
                                                                          $this->getUser(),
                                                                          $descripcionDatos->getEstado());

        [$form] = ($descripcionDatosFormProcessor)($descripcionDatos, $request);
        if ($form->isSubmitted() && $form->isValid()) {
            //toma la url del boton siguiente que depende de lo ultimo realizado por el usuario
            $locationSiguiente = $toolController->DameSiguienteOrigendatos($descripcionDatos);             
            return $this->redirect($locationSiguiente);
        } else {
            $locationAnterior = $this->generateUrl('update_asistentecamposdatos_paso2',["id"=>$id]);
            return $this->render('descripcion/paso3.html.twig', 
              [ 'locationAnterior' => $locationAnterior,
                'ClassBody' => $this->ClassBody,
                'urlCrear' =>  $this->urlCrear,
                'urlAyuda' =>  $this->urlAyuda,
                'urlMenu' =>  $this->urlMenu,
                'permisoEdicion' => $permisoEdicion,
                'paso3_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }
}