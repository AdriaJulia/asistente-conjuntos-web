<?php

namespace App\Controller;


use App\Enum\RutasAyudaEnum;
use App\Enum\TipoOrigenDatosEnum;
use App\Enum\EstadoAltaDatosEnum;
use App\Enum\TipoAlineacionEnum;
use App\Service\Controller\ToolController;
use App\Service\Manager\DescripcionDatosManager;
use App\Service\Processor\DistribucionFormProcessor;
use App\Service\Processor\DescripcionDatosPaso1FormProcessor;
use App\Service\Processor\DescripcionDatosPaso2FormProcessor;
use App\Service\Processor\DescripcionDatosWorkFlowFormProcessor;
use App\Service\CurrentUser;
use App\Service\Manager\OrigenDatosManager;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Ldap\Security\LdapUser;
use Psr\Log\LoggerInterface;


/*
 * Descripción: Es el controlador de todas la llamadas del paso 1 (1.0. 1.1, 1.2)
 *              para crear o actualizar la descripción de los datos.
 *              También controla la ficha del conjunto de datos y el listado.
 */
class DescripcionDatosController extends AbstractController
{

     private $ClassBody = "";
     private $urlAyuda = "";
     private $urlSoporte = "";
     private $urlCrear = "";
     private $urlMenu = "";

    /***
     * Descripcion: Accion de la llamada al listado de los datos
     * Parametros:
     *             pagina:                    para el paginado de los datos
     *             tamano:                    tamaño de la pagina para el paginado de los datos
     *             descripcionDatosManager :  repositorio de la descripcion de datos
     *             logger:                          objeto apara escribir los logs
     *             toolController:                  herramienta de funcionalidades comunes
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
        [$this->urlAyuda, $this->urlSoporte, $this->urlCrear, $this->urlMenu]  = $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::LISTADO_DESCRIPCION,$this->getUser());
        //tomo el listado
        $descripcionDatos = $descripcionDatosManager->get($pagina, $tamano, $request->getSession());
        $datosGrid = array();
        $esAdminitrador = false;
        if ($descripcionDatos!= array()){
            if (count($descripcionDatos['data'])>0) {
                //por cada uno de loe elementos del listado 
                foreach($descripcionDatos['data'] as $data) {
                    //recojo el estado, el enlace a la ficha, etc..
                    [$estadoKey, $estadoDescripcion] = $toolController->DameEstadoDatos($data['estado']);
                    $actual_link = $this->generateUrl('asistentecamposdatos_id',["id"=>$data['id']]);
                    $inicio = (new DateTime($data['creadoEl']))->format('Y-m-d');
                    $fin =  (new DateTime($data['actualizadoEn']))->format('Y-m-d'); 
                    $conjuntoDatos = (!empty($data['origenDatos'])) ? $data['origenDatos']['nombre'] : "";
                    $principal = ($data['distribucion']>0) ? "No" : "Si";
                    [$usuario , $esAdminitrador ]  = $toolController->DameUsuarioActual($this->getUser()); 
                     $usuario = $data['usuario'];
                    //relleno el array odt que se va a mostrar
                    $datosGrid[] = array("estadoKey"=>$estadoKey,  
                                         "estadoDescripcion"=> $estadoDescripcion,
                                         "link" =>  $actual_link, 
                                         "descripcion" =>  $data['titulo'],
                                         "conjuntoDatos" => $conjuntoDatos,
                                         "principal" => $principal,
                                         "usuario" => $usuario,
                                         "fechaInicio" => $inicio, 
                                         "fechaFin"=>  $fin);
                }
            } 
        } else {
            $descripcionDatos = array('totalElementos'=>0);
        }
        return $this->render('grid.html.twig',['esAdministrador' => $esAdminitrador,
                                               'ClassBody' => $this->ClassBody,
                                               'urlCrear' =>  $this->urlCrear,
                                               'urlAyuda' =>  $this->urlAyuda,
                                               'urlSoporte' =>  $this->urlSoporte,
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
     *             toolController:            clase de herramientas para procesos comunes de los controladores
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
        $extension = "";
        $baseDatos = null;
        $verbotonesAdminValida = null;
        $verbotonesAdminDesechar = null;;
        $verbotonesAdminCorregir = null;
        $verbotonesModificacion = null;
        $verbotonesPublicacion = null;
        $verbotonesAdminEditar = null;
        $verEditar = null;
        $permisoEdicion = 'none';
        $ontologia =  "";
        $subentidad = "";
        $descripcionOrigen = "";
        $nombreOrigen = "";
        $xmlAlineacion = "";
        //el class de body en este controlador no es siempre el mismo    
        $this->ClassBody = "fichaRecurso comunidad usuarioConectado";  
        //tomo las urls del menu superior 
        [$this->urlAyuda, $this->urlSoporte, $this->urlCrear, $this->urlMenu]  =  $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::FICHA_DESCRIPCION,$this->getUser());
        //recojo el objeto descripcion datos
        $data = $descripcionDatosManager->find($id, $request->getSession());
        
        if ($data->getId()) {
            $permisoEdicion = $toolController->DamePermisoUsuarioActual($data->getUsuario(), $this->getUser(),true);

            if ($permisoEdicion!== "none") {
                $tabla = null;
                //recojo los valores de los campos del cojunto de datos formateados para la ficha 
                $datos  = $data->getToView($toolController);
                $licencias = explode("@@",$data->getLicencias());
                // hago el enlace para las licencias
                if (count($licencias)==2) {
                    $datos['licencias_url'] = $licencias[0];
                    $datos['licencias_texto'] =  $licencias[1];
                } else {
                    $datos['licencias_url'] = "";
                    $datos['licencias_texto'] = "";
                }
                //recojo el enlace de boton "editar", que depende del últiomo estado donde se quedó el usuario en el asistente
                [$origenDatos, $editLink] = $toolController->DameEnlaceEdicion($data);
                //recojo la url contra la que se va alanzar cualquiera de las acciones con los botones de la ficha (validar, solicitar, etc...)
                $urlworkflow = $this->generateUrl('asistentecamposdatos_workflow',["id"=>$data->getId()]);
                //inicializo para la plantilla twig 
                $tabla = array("campos" =>array(), "filas"=>0);
                $tableAlineacion = array();
                //recojo la informacion del paso 3 (la ontologia - entidad principal) y la lista de campos alineados
                if ($origenDatos->getId()!=null) {
                    [$campos , $ontologia , $tableAlineacion] = $toolController->getOntologiasFicha($data->getOrigenDatos());
                    [$filas, $camposActuales, $errorProceso] = $origendatosManager->DatosFicha($data->getOrigenDatos()->getId(),$request->getSession());   
                    if ($origenDatos->getTipoAlineacion() == TipoAlineacionEnum::XML) {
                        $xmlAlineacion = $data->getOrigenDatos()->getAlineacionXml();
                        if ((!empty($xmlAlineacion)) && file_exists($xmlAlineacion)){
                            $xmlAlineacion = file_get_contents($xmlAlineacion);
                        } else {
                            $xmlAlineacion .= "\n\n----->Archivo no encontrado<-----";
                        }
                    }
                    $subentidad =  $data->getOrigenDatos()->getSubtipoEntidad();
                    $camposActual =  !empty($camposActuales) ? explode(";",$camposActuales) : array();
                    $camposDistintos = ($campos != $camposActual); 
                    $extension = $data->getOrigenDatos()->getExtension();
                    $tabla = array("campos" => $campos, "filas"=>$filas);
                    $descripcionOrigen = (!empty($data->getOrigenDatos()->getDescripcion())) ? $data->getOrigenDatos()->getDescripcion() : "";
                    $nombreOrigen = (!empty($data->getOrigenDatos()->getNombre()))  ? $data->getOrigenDatos()->getNombre() : "";
                    if ($data->getOrigenDatos()->getTipoOrigen()==TipoOrigenDatosEnum::BASEDATOS) {
                        $baseDatos = array("TipoBaseDatos" => $data->getOrigenDatos()->getTipoBaseDatos(),
                                            "Host" => $data->getOrigenDatos()->getHost(),
                                            "Puerto" => $data->getOrigenDatos()->getPuerto(),
                                            "Esquema" => $data->getOrigenDatos()->getEsquema(),
                                            "Servicio" => $data->getOrigenDatos()->getServicio(),
                                            "Tabla" => $data->getOrigenDatos()->getTabla(),
                                            "GaodCoreId" => $data->getGaodcoreResourceId(),
                                            "UsuarioDB" => $data->getOrigenDatos()->getUsuarioDB(),
                                            "ContrasenaDB" => $data->getOrigenDatos()->getContrasenaDB());
                    }
                }
        }
        // solo se puede acceder si el usuario es el mismo que lo creó
        
        //recojo si el usuario es administrador para mostrar botones de su perfil o de perfil usuario (no administrador)
        [$usuario, $esAdminitrador] = $toolController->DameUsuarioActual($this->getUser());
        // recojo los fags  de los botones para mostrarlos o no
        [$verbotonesAdminValida, 
            $verbotonesAdminDesechar,
            $verbotonesAdminCorregir,
            $verbotonesModificacion, 
            $verbotonesPublicacion,
            $verbotonesAdminEditar,
            $verEditar] = $toolController->DameBotonesFicha($esAdminitrador,
                                                            $data->getEstado());
        }
       
        return $this->render('descripcion/ficha.html.twig',['ClassBody' => $this->ClassBody,
                                                            'permisoEdicion' => $permisoEdicion,
                                                            'urlCrear' =>  $this->urlCrear,
                                                            'urlAyuda' =>  $this->urlAyuda,
                                                            'urlSoporte' =>  $this->urlSoporte,
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
                                                            'verbotonesAdminEditar' => $verbotonesAdminEditar,
                                                            'descripcionOrigen' => $descripcionOrigen,
                                                            'nombreOrigen'=> $nombreOrigen,
                                                            'editLink' => $editLink,
                                                            'verEditar' => $verEditar,
                                                            'ontologia' => $ontologia,
                                                            'subentidad' => $subentidad,
                                                            'tableAlineacion' => $tableAlineacion,
                                                            'xmlAlineacion' => $xmlAlineacion,
                                                            'data'=>$datos,
                                                            'extension' => $extension,
                                                            'table'=>$tabla,
                                                            'baseDatos'=>$baseDatos]);
    }

    /***
     * Descripcion: Action de la solicitud  de un cambio de estado, al pulsar un botón de la ficha del conjunto de datos 
     *              Es el action del popup donde se solicita el mensaje para el cambio de estado.
     *              Este proceso envía el correo electrónico en la parte de Apirest
     * Parametros:
     *             id:                                      id de la descripcion de los datos
     *             descripcionDatosWorkFlowFormProcessor:   objeto que realiza el proceso back de la solicitud
     *             descripcionDatosManager:                 repositorio de la descripcion de datos
     *             logger:                                  objeto apara escribir los logs
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
        [$form,$error] = ($descripcionDatosWorkFlowFormProcessor)($descripcionDatos, $request);
        if (!empty($error)){
            $logger->error($error);
        }
        return $this->redirectToRoute('asistentecamposdatos_id',["id"=>$id]); 
    }

    /***
     * Descripcion: Action de la solicitud  de un cambio de estado, al pulsar un botón de la ficha del conjunto de datos 
     *              Este controlador se invoca desde una llamada rest javasscript para no mostrar el popup
     *              Este proceso envía el correo electrónico en la parte de Apirest
     * Parametros:
     *             id:                                      id de la descripcion de los datos
     *             descripcionDatosWorkFlowFormProcessor:   objeto que realiza el proceso back de la solicitud
     *             descripcionDatosManager:                 repositorio de la descripcion de datos
     *             logger:                                  objeto apara escribir los logs
     *             request:                                 El objeto request de la llamada
     */
    /**     
     * @Route("/asistentecamposdatos/workflow/noredirect/{id}", requirements={"id"="\d+"}, name="asistentecamposdatos_workflow_noredirect")
     */
    public function InsertWorkflowNoRedirectAction($id,
                                                   DescripcionDatosWorkFlowFormProcessor $descripcionDatosWorkFlowFormProcessor,
                                                   DescripcionDatosManager $descripcionDatosManager,
                                                   LoggerInterface $logger,
                                                   ToolController $toolController,
                                                   Request $request) {
        //se toma el objeto por id sde la BD con su estado actual                                           
        $descripcionDatos = $descripcionDatosManager->find($id,$request->getSession());
        //se procesa el cambio de estado
        [$form,$error] = ($descripcionDatosWorkFlowFormProcessor)($descripcionDatos, $request);
        if (!empty($error)){
            $logger->error($error);
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $urlredirect = $toolController->DameEnlaceEdicion($descripcionDatos);  
            $content = json_encode(array('data' => $urlredirect[1]));
            $response = new Response($content,Response::HTTP_OK);
            $response->headers->set('Content-Type', 'application/json');
        } else {
            $content = array();
            $response = new Response($content,Response::HTTP_BAD_REQUEST);
            $response->headers->set('Content-Type', 'application/json');
        }
        return $response;
    }


    /***
     * Descripcion: Formulario que da la opción de seleccionar una nueva distribución o comenzar con una ya existente
     * 
     * Parametros:
     *             distribucionFormProcessor:       objeto que realiza el proceso back de la solicitud
     *             descripcionDatosManager:         repositorio de la descripcion de datos
     *             logger:                          objeto apara escribir los logs
     *             toolController:                  herramienta de funcionalidades comunes
     *             request:                         el objeto request de la llamada
     */

    /**
     * @Route("/asistentecamposdatos/paso0", name="insert_asistentecamposdatos_paso0")
     */
    public function InsertPaso0Action(DistribucionFormProcessor $distribucionFormProcessor,
                                      DescripcionDatosManager $descripcionDatosManager,
                                      LoggerInterface $logger,
                                      ToolController $toolController,
                                      Request $request) {           
        //el class de body en este controlador no es siempre el mismo    
        $this->ClassBody = "asistente comunidad usuarioConectado";
        //tomo las urls del menu superior  
               [$this->urlAyuda, $this->urlSoporte, $this->urlCrear, $this->urlMenu]  =  $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::DESCRIPCION_DISTRIBUCION,$this->getUser());

        $descripcionDatos = $descripcionDatosManager->new();
        //creo el formulario 
        [$form,$distribucionClonada,$nueva] = ($distribucionFormProcessor)($request);
        //si el formulario se ha lazado obtengo los datos y procedo
        if ($form->isSubmitted() && $form->isValid()) {
            if ($nueva){
                return $this->redirectToRoute('insert_asistentecamposdatos_paso1'); 
            } else {
               return  $this->redirectToRoute('insert_asistentecamposdatos_url',["iddes"=>$distribucionClonada->getId()]);  
            }
        } else {
            //lanzo el formulario al usuario para que lo utilice
            return $this->render('descripcion/paso0.html.twig', [
                            'ClassBody' => $this->ClassBody,
                            'urlCrear' =>  $this->urlCrear,
                            'urlAyuda' =>  $this->urlAyuda,
                            'urlSoporte' =>  $this->urlSoporte,
                            'urlMenu' =>  $this->urlMenu,
                            'permisoEdicion' => "block",
                            'paso0_form' => $form->createView(),
                            'errors' => $form->getErrors()
                    ]);
        }       
    }

    /***
     * Descripcion: Inserta una descripción de datos en el formulario 1.1
     * 
     * Parametros:
     *             descripcionDatosFormProcessor:   objeto que realiza el proceso back de la solicitud
     *             descripcionDatosManager:         repositorio de la descripcion de datos
     *             logger:                          objeto apara escribir los logs
     *             toolController:                  herramienta de funcionalidades comunes
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
        $urltags = $this->getParameter('url_tags');            
        //el class de body en este controlador no es siempre el mismo    
        $this->ClassBody = "asistente comunidad usuarioConectado";
        //tomo las urls del menu superior  
               [$this->urlAyuda, $this->urlSoporte, $this->urlCrear, $this->urlMenu]  =  $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::DESCRIPCION_CONTENIDO,$this->getUser());

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
                'urlSoporte' =>  $this->urlSoporte,
                'urlMenu' =>  $this->urlMenu,
                'urltags' =>  $urltags,
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
     *             logger:                          objeto apara escribir los logs
     *             toolController:                  herramienta de funcionalidades comunes
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
            
        $urltags = $this->getParameter('url_tags');
         //el class de body en este controlador no es siempre el mismo    
        $this->ClassBody = "asistente comunidad usuarioConectado"; 
        //tomo las urls del menu superior 
        [$this->urlAyuda, $this->urlSoporte, $this->urlCrear, $this->urlMenu]  = $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::DESCRIPCION_CONTENIDO,$this->getUser());

        $descripcionDatos = $descripcionDatosManager->find($id, $request->getSession());

        $esPadre = ($descripcionDatos->getDistribucion()<=0);
        
        // solo se puede acceder si el estado es correcto y el usuario es el mismo que lo creó
        $permisoEdicion = $toolController->DamePermisoUsuarioActualEstado($descripcionDatos->getUsuario(), 
                                                                          $this->getUser(),
                                                                          $descripcionDatos->getEstado(),
                                                                          $esPadre);
        [$form] = ($descripcionDatosFormProcessor)($descripcionDatos, $request);
        if ($form->isSubmitted() && $form->isValid()) {
            //   $this->addFlash('success', 'Descripción actualizada'); 
            return $this->redirectToRoute('update_asistentecamposdatos_paso2',["id"=>$id]); 
        } else {
            return $this->render('descripcion/paso1.html.twig', [
                'ClassBody' => $this->ClassBody,
                'urlCrear' =>  $this->urlCrear,
                'urltags' =>  $urltags,
                'urlAyuda' =>  $this->urlAyuda,
                'urlSoporte' =>  $this->urlSoporte,
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
     *             logger:                          objeto apara escribir los logs
     *             toolController:                  herramienta de funcionalidades comunes
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
               [$this->urlAyuda, $this->urlSoporte, $this->urlCrear, $this->urlMenu]  =  $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::DESCRIPCION_CONTEXTO,$this->getUser());

        $descripcionDatos = $descripcionDatosManager->find($id, $request->getSession());

        $esPadre = ($descripcionDatos->getDistribucion()<=0);
        // solo se puede acceder si el estado es correcto y el usuario es el mismo que lo creó
        $permisoEdicion = $toolController->DamePermisoUsuarioActualEstado($descripcionDatos->getUsuario(), 
                                                                          $this->getUser(),
                                                                          $descripcionDatos->getEstado(),
                                                                          $esPadre);
        [$form] = ($descripcionDatosFormProcessor)($descripcionDatos, $request);

        [$form] = ($descripcionDatosFormProcessor)($descripcionDatos, $request);
        if ($form->isSubmitted() && $form->isValid()) {
           // $this->addFlash('success', 'It sent!'); 
            $locationSiguiente = $toolController->DameSiguienteOrigendatos($descripcionDatos);             
            return $this->redirect($locationSiguiente);
        } else {
            $locationAnterior = $this->generateUrl('update_asistentecamposdatos_paso1',["id"=>$id]);
            return $this->render('descripcion/paso2.html.twig', [
                'locationAnterior' => $locationAnterior,
                'ClassBody' => $this->ClassBody,
                'urlCrear' =>  $this->urlCrear,
                'urlAyuda' =>  $this->urlAyuda,
                'urlSoporte' =>  $this->urlSoporte,
                'urlMenu' =>  $this->urlMenu,
                'permisoEdicion' => $permisoEdicion,
                'paso2_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }
}