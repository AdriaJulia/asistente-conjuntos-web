<?php

namespace App\Controller;

use App\Service\Manager\OrigenDatosManager;
use App\Service\Manager\DescripcionDatosManager;
use App\Enum\RutasAyudaEnum;
use App\Enum\ModoFormularioAlineacionEnum;;
use App\Service\Processor\AlineacionDatosCamposFormProcessor;
use App\Service\Processor\AlineacionDatosXmlFormProcessor;
use App\Service\Controller\ToolController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Psr\Log\LoggerInterface;

/*
 * Descripción: Es el controlador del paso3, muestra el formulario y guarda la entidad principal y las relaciones
 *              campo - entidad  en un array json.
 *              Todo el funcionamiento dinamico dellos cotroles se reliza con javascrip y con twig.
 *              
 */
class AlineacionDatosController extends AbstractController
{

    private $ClassBody = "asistente comunidad usuarioConectado";
    private $urlAyuda = "";
    private $urlSoporte = "";
    private $urlCrear = "";
    private $urlMenu = "";

     /***
     * Descripcion: Inserta una entidad principal y un conjunto de campos alienados en fomato json
     *              El conjunto de datos alineado json, se crea en un campo oculto en front según va seleccionando campos .
     *              
     * Parametros:
     *             iddes:                         id la descripcion de los dato de datos a actualizar
     *             id:                            id del del origen  de datos que se dese alinear
     *             alineacionDatosFormProcessor:  proceso back del origen de datos a una llamada
     *             origenDatosManager :           repositorio del origen de datos
     *             descripcionDatosManager :      repositorio de la descripcion de datos
     *             toolController:                clase de herramientas para procesoso comunes de los controladores
     *             request:                       El objeto request de la llamada
     */

    /**
    * @Route("/asistentecamposdatos/{iddes}/{origen}/origen/{id}/campos/alineacion", requirements={"iddes"="\d+", "id"="\d+", "origen"="url|file|database"}, name="insert_campos_alineacion")
    */
   public function InsertCamposAction(int $iddes,
                                      int $id,
                                      string $origen,
                                      AlineacionDatosCamposFormProcessor $alineacionDatosFormProcessor,
                                      OrigenDatosManager $origenDatosManager,
                                      DescripcionDatosManager $descripcionDatosManager,
                                      ToolController $toolController,
                                      LoggerInterface $logger,
                                      Request $request) {

        $locationAnterior = "";
        $errorProceso = "";
        $usuario = "";
        $esAdminitrador = false;
        [$usuario, $esAdminitrador] = $toolController->DameUsuarioActual($this->getUser());
        
        //tomo el objeto donde está el origen de datos
        $origenDatos = $origenDatosManager->find($id, $request->getSession());

        $tipoOrigen = ($origenDatos->getId() != null) ? $origenDatos->getTipoOrigen() : $origen;
        //tomo las urls del menu superior 
        [$this->urlAyuda, $this->urlSoporte, $this->urlCrear, $this->urlMenu]  = $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::ALINEACION_EI2A,$this->getUser());

        //toma la url del origen de datos donde se quedo el usuario
        $locationAnterior = $toolController->DameUrlAnteriorOrigendatos($tipoOrigen, $id, $iddes, $_SERVER);

        //lanzo el proceso de actualización 
        [$form,$modoFormulario, $origenDatos,$errorProceso] = ($alineacionDatosFormProcessor)($iddes, $origenDatos, $esAdminitrador, $request);
        if (!empty($errorProceso)) {
           $logger->error($errorProceso);
        }
        $subtipo = empty($origenDatos->getSubtipoEntidad()) ? "" : $origenDatos->getSubtipoEntidad();
        //si no tengo origen de datos saco popup no se pude editar
        if ($origenDatos->getId() == null) {
         return $this->render('alineacion/seleccion.html.twig', [
            'errorProceso' => $errorProceso,
            'locationAnterior' => $locationAnterior,
            'alineacion_form' => $form->createView(),
            'ClassBody' => $this->ClassBody,
            'urlCrear' =>  $this->urlCrear,
            'urlAyuda' =>  $this->urlAyuda,
            'urlSoporte' =>  $this->urlSoporte,
            'urlMenu' =>  $this->urlMenu,
            'subtipo' => $subtipo,
            'permisoEdicion' => "none"
         ]);  
        } 
        //recojo la descripción del origen datos 
        $descripcionDatos = $descripcionDatosManager->find($iddes, $request->getSession());
        // solo se puede acceder si el estado es correcto y el usuario es el mismo que lo creó
        $permisoEdicion = $toolController->DamePermisoUsuarioActualEstado($descripcionDatos->getUsuario(), 
                                                                          $this->getUser(),
                                                                          $descripcionDatos->getEstado());                                                                 
        if ($form->isSubmitted() && $form->isValid()) {
           //el usuario pude omitir el paso o guardar su alineación 
           if(($modoFormulario==ModoFormularioAlineacionEnum::Guardar) || ($modoFormulario==ModoFormularioAlineacionEnum::Omitir)) {
              return $this->redirectToRoute('asistentecamposdatos_id',["id"=> $descripcionDatos->getId()]); 
           }
        } else {
           return $this->render('alineacion/seleccion.html.twig', [
            'errorProceso' => $errorProceso,
            'locationAnterior' => $locationAnterior,
            'alineacion_form' => $form->createView(),
            'ClassBody' => $this->ClassBody,
            'urlCrear' =>  $this->urlCrear,
            'urlAyuda' =>  $this->urlAyuda,
            'urlSoporte' =>  $this->urlSoporte,
            'urlMenu' =>  $this->urlMenu,
            'permisoEdicion' => $permisoEdicion,
            'subtipo' => $subtipo,
           // 'entidadPrincipal' => $entidadPrincipal,
           // 'subEntidad' => $subEntidad
           ]);        
        }
        return $this->render('alineacion/seleccion.html.twig', [
            'errorProceso' => $errorProceso,
            'locationAnterior' => $locationAnterior,
            'alineacion_form' => $form->createView(),
            'ClassBody' => $this->ClassBody,
            'urlCrear' =>  $this->urlCrear,
            'urlAyuda' =>  $this->urlAyuda,
            'urlSoporte' =>  $this->urlSoporte,
            'urlMenu' =>  $this->urlMenu,
            'permisoEdicion' => $permisoEdicion,
            'subtipo' => $subtipo,
           // 'entidadPrincipal' => $entidadPrincipal,
           // 'subEntidad' => $subEntidad
        ]);
   }

    /***
     * Descripcion: Inserta una entidad principal y un conjunto de campos alienados en fomato xml
     *              El conjunto de datos alineado xml.
     *              
     * Parametros:
     *             iddes:                         id la descripcion de los dato de datos a actualizar
     *             id:                            id del del origen  de datos que se dese alinear
     *             alineacionDatosFormProcessor:  proceso back del origen de datos a una llamada
     *             origenDatosManager :           repositorio del origen de datos
     *             descripcionDatosManager :      repositorio de la descripcion de datos
     *             toolController:                clase de herramientas para procesoso comunes de los controladores
     *             request:                       El objeto request de la llamada
     */

    /**
    * @Route("/asistentecamposdatos/{iddes}/{origen}/origen/{id}/xml/alineacion", requirements={"iddes"="\d+", "id"="\d+", "origen"="url|file|database"}, name="insert_xml_alineacion")
    */
    public function InsertXmlAction(int $iddes,
                                    int $id,
                                    string $origen,
                                    AlineacionDatosXmlFormProcessor $alineacionDatosFormProcessor,
                                    OrigenDatosManager $origenDatosManager,
                                    DescripcionDatosManager $descripcionDatosManager,
                                    ToolController $toolController,
                                    LoggerInterface $logger,
                                    Request $request) {

         $locationAnterior = "";
         $errorProceso = "";
         $usuario = "";
         $esAdminitrador = false;
         [$usuario, $esAdminitrador] = $toolController->DameUsuarioActual($this->getUser());
         //tomo el objeto donde está el origen de datos
         $origenDatos = $origenDatosManager->find($id, $request->getSession());  
         $tipoOrigen = ($origenDatos->getId() != null) ?$origenDatos->getTipoOrigen() : $origen;
         //tomo las urls del menu superior 
         [$this->urlAyuda, $this->urlSoporte, $this->urlCrear, $this->urlMenu]  = $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::ALINEACION_XML,$this->getUser());
         //toma la url del origen de datos donde se quedo el usuario
         $locationAnterior = $toolController->DameUrlAnteriorOrigendatos($tipoOrigen, $id, $iddes, $_SERVER);


         //lanzo el proceso de actualización 
         [$form,$modoFormulario, $origenDatos,$errorProceso] = ($alineacionDatosFormProcessor)($iddes, $origenDatos,$esAdminitrador,$request);
         if (isset($errorProceso)) {
            $logger->error($errorProceso);
         }
         //si no tengo origen de datos saco popup no se pude editar
         if ($origenDatos->getId() == null) {
            return $this->render('alineacion/seleccion.html.twig', [
               'errorProceso' => $errorProceso,
               'locationAnterior' => $locationAnterior,
               'alineacion_form' => $form->createView(),
               'ClassBody' => $this->ClassBody,
               'urlCrear' =>  $this->urlCrear,
               'urlAyuda' =>  $this->urlAyuda,
               'urlSoporte' =>  $this->urlSoporte,
               'urlMenu' =>  $this->urlMenu,
               'subtipo' => "",
               'permisoEdicion' => "none"
            ]);  
         } 
         //recojo la descripción del origen datos 
         $descripcionDatos = $descripcionDatosManager->find($iddes, $request->getSession());
         // solo se puede acceder si el estado es correcto y el usuario es el mismo que lo creó
         $permisoEdicion = $toolController->DamePermisoUsuarioActualEstado($descripcionDatos->getUsuario(), 
                                                                           $this->getUser(),
                                                                           $descripcionDatos->getEstado());
                                              
         if ($form->isSubmitted() && $form->isValid()) {
           //el usuario pude omitir el paso o guardar su alineación 
            if(($modoFormulario==ModoFormularioAlineacionEnum::Guardar) || ($modoFormulario==ModoFormularioAlineacionEnum::Omitir)) {
               return $this->redirectToRoute('asistentecamposdatos_id',["id"=> $descripcionDatos->getId()]); 
            }
            } else {
               return $this->render('alineacion/seleccion.html.twig', [
                                    'errorProceso' => $errorProceso,
                                    'locationAnterior' => $locationAnterior,
                                    'alineacion_form' => $form->createView(),
                                    'ClassBody' => $this->ClassBody,
                                    'urlCrear' =>  $this->urlCrear,
                                    'urlAyuda' =>  $this->urlAyuda,
                                    'urlSoporte' =>  $this->urlSoporte,
                                    'urlMenu' =>  $this->urlMenu,
                                    'permisoEdicion' => $permisoEdicion,
                                    'subtipo' => "",
               ]);        
         }
         return $this->render('alineacion/seleccion.html.twig', [
                              'errorProceso' => $errorProceso,
                              'locationAnterior' => $locationAnterior,
                              'alineacion_form' => $form->createView(),
                              'ClassBody' => $this->ClassBody,
                              'urlCrear' =>  $this->urlCrear,
                              'urlAyuda' =>  $this->urlAyuda,
                              'urlSoporte' =>  $this->urlSoporte,
                              'urlMenu' =>  $this->urlMenu,
                              'permisoEdicion' => $permisoEdicion,
                              'subtipo' => "",
         ]);
   }
}

