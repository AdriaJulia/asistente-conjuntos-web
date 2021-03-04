<?php

namespace App\Controller;

use App\Enum\EstadoDescripcionDatosEnum;
use App\Enum\RutasAyudaEnum;
use App\Enum\TipoOrigenDatosEnum;
use App\Enum\EstadoAltaDatosEnum;
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



class DescripcionDatosController extends AbstractController
{

     private $ClassBody = "asistente comunidad usuarioConectado";
     private $urlAyuda = "";
     private $urlCrear = "";

     /**
     * @Route("/asistentecamposdatos", requirements={"pagina"="\d+","tamano"="\d+" }, name="asistentecamposdatos_index")
     */
    public function IndexAction(int $pagina=1,
                                int $tamano=0,
                                DescripcionDatosManager $descripcionDatosManager,
                                LoggerInterface $logger,
                                Request $request) {
               
    
        $this->ClassBody = "listado comunidad usuarioConectado";  
        $this->urlCrear =  $this->generateUrl("insert_asistentecamposdatos_paso1");
        $this->urlAyuda = $this->generateUrl('asistentecamposdatos_ayuda_index',["pagina"=>RutasAyudaEnum::LISTADO_ACCIONES]);   
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->urlAyuda .= "?locationAnterior={$actual_link}";   
        $descripcionDatos = $descripcionDatosManager->get($pagina, $tamano, $request->getSession());
        $datosGrid = array();
        if ($descripcionDatos!= array()){
            if (count($descripcionDatos['data'])>0) {
                foreach($descripcionDatos['data'] as $data) {
                    [$estadoKey, $estadoDescripcion] = $this->DameEstadoUsuario($data['estado']);
                    $link = $this->generateUrl('asistentecamposdatos_id',["id"=>$data['id']]);
                    $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$link";
       
                    $inicio = (new DateTime($data['creadoEl']))->format('Y-m-d');
                    $fin =  (new DateTime($data['actualizadoEn']))->format('Y-m-d'); 
    
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
                                               'descripcionDatos'=>  $datosGrid,
                                               "totalElementos"=>  $descripcionDatos['totalElementos']
                                              ]);
    }

     /**
     * @Route("/asistentecamposdatos/{id}", requirements={"id"="\d+"}, name="asistentecamposdatos_id")
     */
    public function GetAction(int $id,
                              DescripcionDatosManager $descripcionDatosManager,
                              OrigenDatosManager $origendatosManager,
                              LoggerInterface $logger,
                              Request $request) {
           
        $this->ClassBody = "fichaRecurso comunidad usuarioConectado"; 
        $this->urlCrear =  $this->generateUrl("insert_asistentecamposdatos_paso1");
        $this->urlAyuda = $this->generateUrl('asistentecamposdatos_ayuda_index',["pagina"=>RutasAyudaEnum::FICHA_ACCIONES]);  
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->urlAyuda .= "?locationAnterior={$actual_link}";
        $data = $descripcionDatosManager->find($id, $request->getSession());
        $tabla = null;
        $errorProceso = "";
        [$estadoKey, $estadoDescripcion] = $this->DameEstadoUsuario($data->getEstado());
        $identificador = $data->getIdentificacion();
        $denominacion = $data->getDenominacion();
        $descripcion = $data->getDescripcion();
        $frecuencia = !empty($data->getFrecuenciaActulizacion()) ? $data->getFrecuenciaActulizacion() : "";
        $inicio =  ($data->getFechaInicio()!=null) ? $data->getFechaInicio()->format('Y-m-d') : "";
        $fin =  ($data->getFechaFin()!=null)  ? $data->getFechaFin()->format('Y-m-d') : ""; 
        $Instancias = !empty($data->getInstancias()) ? explode(",",$data->getInstancias()) : array();
        $organo =  !empty($data->getOrganoResponsable()) ?  $data->getOrganoResponsable() : "";
        $condiciones =  !empty($data->getCondiciones())  ? $data->getCondiciones() : "";
        $finalidad =  !empty($data->getFinalidad())  ? $data->getFinalidad() : "";;
        $licencias =  !empty($data->getLicencias()) ? $data->getLicencias() : ""; ;
        $vocabularios = !empty($data->getVocabularios()) ? explode(",",$data->getVocabularios()) : array();
        $servicios = !empty($data->getServicios()) ? explode(",",$data->getServicios()) : array();
        $etiquetas = !empty($data->getEtiquetas()) ? explode(",",$data->getEtiquetas()) : array();
        $estructura =  !empty($data->getEstructura()) ?  $data->getEstructura() : "";
        $estructuraDenominacion =  !empty($data->getEstructuraDenominacion()) ?  $data->getEstructuraDenominacion():  "";
        $formatos =  !empty($data->getFormatos())  ? $data->getFormatos(): "";

        $datos  = array("estado"=>$estadoDescripcion,
                        "estadoKey" =>  $estadoKey,
                        "identificador"=> $identificador,
                        "denominacion" =>  $denominacion, 
                        "descripcion" => $descripcion,
                        "frecuencia" => $frecuencia,
                        "fechaInicio" =>$inicio,
                        "fechaFin" =>$fin,
                        "instancias" => $Instancias,
                        "organo" => $organo,
                        "condiciones"=> $condiciones,
                        "finalidad" => $finalidad,
                        "licencias" =>  $licencias,
                        "vocabularios" =>  $vocabularios,
                        "servicios" =>   $servicios,
                        "etiquetas" =>  $etiquetas,
                        "estructura" =>  $estructura,
                        "estructuraDenominacion" =>  $estructuraDenominacion,
                        "formatos" => $formatos);
    
        $link = "";
        $origenDatos  = null;
        $linkCreaOrigendatos = "";
        if (!empty($data->getOrigenDatos())){
            $origenDatos = ($data->getOrigenDatos());      
        } else {
            $linkCreaOrigendatos = $this->generateUrl('insert_asistentecamposdatos_url',["iddes"=>$data->getId()]);
        }

        if (($data->getEstado()==EstadoDescripcionDatosEnum::BORRADOR) || 
            ($data->getEstado()==EstadoDescripcionDatosEnum::EN_CORRECCION)) {
            switch ($data->getEstadoAlta()) {  
                case EstadoAltaDatosEnum::paso1:
                    $link = $this->generateUrl('update_asistentecamposdatos_paso1',["id"=>$data->getId()]);
                    break;
                case EstadoAltaDatosEnum::paso2:
                    $link = $this->generateUrl('update_asistentecamposdatos_paso2',["id"=>$data->getId()]);
                    break;
                case EstadoAltaDatosEnum::paso3:
                    $link = $this->generateUrl('update_asistentecamposdatos_paso3',["id"=>$data->getId()]);
                    break;
                case EstadoAltaDatosEnum::origen_database:
                    if (empty($linkCreaOrigendatos)){
                        $link = $this->generateUrl('update_asistentecamposdatos_database',["iddes"=>$data->getId(),"id"=>$origenDatos->getId()]);
                    } else {
                        $link = $linkCreaOrigendatos;
                    }
                    break;
                case EstadoAltaDatosEnum::origen_file:
                    if (empty($linkCreaOrigendatos)){
                        $link = $this->generateUrl('update_asistentecamposdatos_file',["iddes"=>$data->getId(),"id"=>$origenDatos->getId()]);
                    } else {
                        $link = $linkCreaOrigendatos;
                    }  
                    break;
                case EstadoAltaDatosEnum::origen_url:
                    if (empty($linkCreaOrigendatos)){
                        $link = $this->generateUrl('update_asistentecamposdatos_url',["iddes"=>$data->getId(), "id"=>$origenDatos['id']]);
                    } else {
                        $link = $linkCreaOrigendatos;
                    }  
                    break;
                case EstadoAltaDatosEnum::alineacion:
                    if (empty($linkCreaOrigendatos)){
                        $link = $this->generateUrl('insert_alineacion',["iddes"=>$data->getId(), "id"=>$origenDatos->getId(), "origen" =>  $origenDatos->getTipoOrigen()]);
                    } else {
                        $link = $linkCreaOrigendatos;
                    } 
                    break;                                                                                                                                  
                default:
                    $link = $this->generateUrl('update_asistentecamposdatos_paso1',["id"=>$data->getId()]);
                    break;
            }
        }
        $urlworkflow = $this->generateUrl('asistentecamposdatos_workflow',["id"=>$data->getId()]);
        $editLink = $link;
        $campos = "";
        $filas = "";
        $tabla  = array();
        $camposDistintos = false;
        $muestraError = false;
        $camposActual =""; 
        $tabla = array("campos" =>array(), "filas"=>0);
        $tableAlineacion = array();
        $ontologia =  "";
        if ($origenDatos->getId()!=null) {
            $campos = !empty($data->getOrigenDatos()->getCampos()) ? explode(";",$data->getOrigenDatos()->getCampos()) : array();
            $ontologia =  (!empty($origenDatos->getAlineacionEntidad()))  ? $origenDatos->getAlineacionEntidad(): "";
            $tableAlineacion = (!empty($origenDatos->getAlineacionRelaciones()))  ? get_object_vars(json_decode(str_replace(",}","}",$origenDatos->getAlineacionRelaciones()))) : array();

            [$filas, $camposActuales, $errorProceso] = $origendatosManager->DatosFicha($data->getOrigenDatos()->getId(),$request->getSession());
            $camposActual =  !empty($camposActuales) ? explode(";",$camposActuales) : array();
            $camposDistintos = ($campos != $camposActual); 
            $tabla = array("campos" => $campos, "filas"=>$filas);
            $muestraError = $camposDistintos  || !empty($errorProceso); 
        }

        $this->urlCrear =  $this->generateUrl("insert_asistentecamposdatos_paso1");

        $verbotonesModificacion = "none";
        $verbotonesPublicacion = "none";
        $verbotonesAdminValidar = "none";
        $verbotonesAdminDesechar = "none";
        $verbotonesAdminCorregir = "none";
        $verEditar= "none";
        [$usuario,  $esAdminitrador] = $this->DameUsuarioActual();
        if ($esAdminitrador) {
            if ($data->getEstado() == EstadoDescripcionDatosEnum::EN_ESPERA_PUBLICACION ){
                $verbotonesAdminValidar = "block";
                $verbotonesAdminDesechar = "block";
                $verbotonesAdminCorregir = "block";
            } else if ($data->getEstado() == EstadoDescripcionDatosEnum::EN_ESPERA_MODIFICACION) {
                $verbotonesAdminValidar = "block";
                $verbotonesAdminDesechar = "block";
                $verbotonesAdminCorregir = "none";
            }
        } else {
            if ( $data->getEstado() == EstadoDescripcionDatosEnum::VALIDADO){
                $verbotonesModificacion = "block";
            }
            if ( $data->getEstado() == EstadoDescripcionDatosEnum::BORRADOR ||  
                 $data->getEstado() == EstadoDescripcionDatosEnum::EN_CORRECCION ){
                $verbotonesPublicacion = "block";
                $verEditar = "block";
            }
        }
   
 

        return $this->render('descripcion/ficha.html.twig',['ClassBody' => $this->ClassBody,
                                                            'urlCrear' =>  $this->urlCrear,
                                                            'urlAyuda' =>  $this->urlAyuda,
                                                            'camposDistintos' => $camposDistintos,
                                                            'errorProceso' => $errorProceso,
                                                            'camposAprobados' => $campos,
                                                            'camposActuales' => $camposActual,
                                                            'muestraError' => $muestraError,
                                                            'urlworkflow' => $urlworkflow,
                                                            'verbotonesModificacion' => $verbotonesModificacion,
                                                            'verbotonesPublicacion' => $verbotonesPublicacion,
                                                            'verbotonesAdminValidar' => $verbotonesAdminValidar,
                                                            'verbotonesAdminDesechar' => $verbotonesAdminDesechar,
                                                            'verbotonesAdminCorregir' => $verbotonesAdminCorregir,
                                                            'editLink' => $editLink,
                                                            'verEditar' => $verEditar,
                                                            'ontologia' => $ontologia,
                                                            'tableAlineacion' => $tableAlineacion,
                                                            'data'=>$datos,
                                                            'table'=>$tabla]);
    }

    /**     
     * @Route("/asistentecamposdatos/workflow/{id}", requirements={"id"="\d+"}, name="asistentecamposdatos_workflow")
     */
    public function InsertWorkflowAction($id,
                                         DescripcionDatosWorkFlowFormProcessor $descripcionDatosWorkFlowFormProcessor,
                                         DescripcionDatosManager $descripcionDatosManager,
                                         LoggerInterface $logger,
                                         Request $request) {
            $descripcionDatos = $descripcionDatosManager->find($id,$request->getSession());
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

    /**
     * @Route("/asistentecamposdatos/paso1", name="insert_asistentecamposdatos_paso1")
     */
    public function InsertPaso1Action(DescripcionDatosPaso1FormProcessor $descripcionDatosFormProcessor,
                                      DescripcionDatosManager $descripcionDatosManager,
                                      LoggerInterface $logger,
                                      Request $request) {

        $this->urlAyuda = $this->generateUrl('asistentecamposdatos_ayuda_index',["pagina"=>RutasAyudaEnum::DESCRIPCION_PASO11]);  
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->urlAyuda .= "?locationAnterior={$actual_link}";
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
                'permisoEdicion' => "block",
                'paso1_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }
             

    /**
     * @Route("/asistentecamposdatos/paso1/{id}", requirements={"id"="\d+"}, name="update_asistentecamposdatos_paso1")
     */
    public function UpdatePaso1Action(int $id,
                                      DescripcionDatosPaso1FormProcessor $descripcionDatosFormProcessor,
                                      DescripcionDatosManager $descripcionDatosManager,
                                      LoggerInterface $logger,
                                      Request $request) {

            $this->urlAyuda = $this->generateUrl('asistentecamposdatos_ayuda_index',["pagina"=>RutasAyudaEnum::DESCRIPCION_PASO11]);     
            $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $this->urlAyuda .= "?locationAnterior={$actual_link}";                          
            $this->urlCrear =  $this->generateUrl("insert_asistentecamposdatos_paso1");
            $descripcionDatos = $descripcionDatosManager->find($id, $request->getSession());
            $permisoEdicion = ($descripcionDatos->getEstado() == EstadoDescripcionDatosEnum::BORRADOR  ||
                               $descripcionDatos->getEstado() == EstadoDescripcionDatosEnum::EN_CORRECCION) ? "block" : "none"; 
            [$form] = ($descripcionDatosFormProcessor)($descripcionDatos, $request);
            if ($form->isSubmitted() && $form->isValid()) {
             //   $this->addFlash('success', 'Descripción actualizada'); 
                return $this->redirectToRoute('update_asistentecamposdatos_paso2',["id"=>$id]); 
            } else {
                return $this->render('descripcion/paso1.html.twig', [
                    'ClassBody' => $this->ClassBody,
                    'urlCrear' =>  $this->urlCrear,
                    'urlAyuda' =>  $this->urlAyuda,
                    'permisoEdicion' => $permisoEdicion,
                    'paso1_form' => $form->createView(),
                    'errors' => $form->getErrors()
                ]);
            }
    }

    /**
     * @Route("/asistentecamposdatos/paso2/{id}", requirements={"id"="\d+"}, name="update_asistentecamposdatos_paso2")
     */
    public function UpdatePaso2Action(int $id,
                                      DescripcionDatosPaso2FormProcessor $descripcionDatosFormProcessor,
                                      DescripcionDatosManager $descripcionDatosManager,
                                      LoggerInterface $logger,
                                      Request $request) {

        $this->urlAyuda = $this->generateUrl('asistentecamposdatos_ayuda_index',["pagina"=>RutasAyudaEnum::DESCRIPCION_PASO12]);  
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->urlAyuda .= "?locationAnterior={$actual_link}";
        $descripcionDatos = $descripcionDatosManager->find($id, $request->getSession());
        $permisoEdicion = ($descripcionDatos->getEstado() == EstadoDescripcionDatosEnum::BORRADOR  ||
                           $descripcionDatos->getEstado() == EstadoDescripcionDatosEnum::EN_CORRECCION) ? "block" : "none"; 
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
                'permisoEdicion' => $permisoEdicion,
                'paso2_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }

    /**
     * @Route("/asistentecamposdatos/paso3/{id}", requirements={"id"="\d+"}, name="update_asistentecamposdatos_paso3")
     */
    public function UpdatePaso3Action(int $id,
                                      DescripcionDatosPaso3FormProcessor $descripcionDatosFormProcessor,
                                      DescripcionDatosManager $descripcionDatosManager,
                                      LoggerInterface $logger,
                                      Request $request) {
        $this->urlAyuda = $this->generateUrl('asistentecamposdatos_ayuda_index',["pagina"=>RutasAyudaEnum::DESCRIPCION_PASO13]); 
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->urlAyuda .= "?locationAnterior={$actual_link}";
        $this->urlCrear =  $this->generateUrl("insert_asistentecamposdatos_paso1");
        $descripcionDatos = $descripcionDatosManager->find($id, $request->getSession());
        $permisoEdicion = ($descripcionDatos->getEstado() == EstadoDescripcionDatosEnum::BORRADOR  ||
                           $descripcionDatos->getEstado() == EstadoDescripcionDatosEnum::EN_CORRECCION) ? "block" : "none"; 
        [$form] = ($descripcionDatosFormProcessor)($descripcionDatos, $request);
        if ($form->isSubmitted() && $form->isValid()) {
        //    $this->addFlash('success', 'It sent!'); 
            $locationSiguiente = "";
            if ($descripcionDatos->TieneOrigenDatos()) {
                if ($descripcionDatos->getOrigenDatos()->getTipoOrigen() == TipoOrigenDatosEnum::BASEDATOS) {
                    $locationSiguiente =  $this->generateUrl('update_asistentecamposdatos_database',["iddes"=>$id, "id"=>$descripcionDatos->getOrigenDatos()->getId()]);
                } elseif ($descripcionDatos->getOrigenDatos()->getTipoOrigen() == TipoOrigenDatosEnum::ARCHIVO)  {
                    $locationSiguiente =  $this->generateUrl('update_asistentecamposdatos_file',["iddes"=>$id, "id"=>$descripcionDatos->getOrigenDatos()->getId()]);
                } else {
                    $locationSiguiente =  $this->generateUrl('update_asistentecamposdatos_url',["iddes"=>$id, "id"=>$descripcionDatos->getOrigenDatos()->getId()]);
                }
            } else {
                $locationSiguiente =  $this->generateUrl('insert_asistentecamposdatos_url',["iddes"=>$id]);
            }               
            return $this->redirect($locationSiguiente);
        } else {
            $locationAnterior = $this->generateUrl('update_asistentecamposdatos_paso2',["id"=>$id]);
            return $this->render('descripcion/paso3.html.twig', 
              [ 'locationAnterior' => $locationAnterior,
                'ClassBody' => $this->ClassBody,
                'urlCrear' =>  $this->urlCrear,
                'urlAyuda' =>  $this->urlAyuda,
                'permisoEdicion' => $permisoEdicion,
                'paso3_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }

    private function DameUsuarioActual() : array {
        if ($this->getUser()->getEntry()) {
            $usuario =  $this->getUser()->getExtraFields()['mail'];
            $esAdminitrador = ($this->getUser()->getExtraFields()['roles'] == "ROLE_ADMIN");
        }
        return  [$usuario , $esAdminitrador ] ;
    }

    private function DameEstadoUsuario($estado) :array {

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
}