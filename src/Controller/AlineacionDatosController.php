<?php

namespace App\Controller;

use App\Enum\EstadoDescripcionDatosEnum;
use App\Service\Manager\OrigenDatosManager;
use App\Service\Manager\DescripcionDatosManager;
use App\Enum\RutasAyudaEnum;
use App\Enum\ModoFormularioAlineacionEnum;;
use App\Service\Processor\AlineacionDatosFormProcessor;
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
    * @Route("/asistentecamposdatos/{iddes}/{origen}/origen/{id}/alineacion", requirements={"iddes"="\d+", "id"="\d+", "origen"="url|file|database"}, name="insert_alineacion")
    */
   public function InsertAction(int $iddes,
                                int $id,
                                string $origen,
                                AlineacionDatosFormProcessor $alineacionDatosFormProcessor,
                                OrigenDatosManager $origenDatosManager,
                                DescripcionDatosManager $descripcionDatosManager,
                                ToolController $toolController,
                                LoggerInterface $logger,
                                Request $request) {

        $locationAnterior = "";
        $errorProceso = "";
        //tomo las urls del menu superior 
        [$this->urlAyuda, $this->urlCrear, $this->urlMenu]  = $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::ALINEACION_DATOS,$this->getUser());
        //toma la url del origen de datos donde se quedo el usuario
        $locationAnterior = $toolController->DameUrlAnteriorOrigendatos($origen, $id, $iddes, $_SERVER);
        //tom el objeto donde está el origen de datos
        $origenDatos = $origenDatosManager->find($id, $request->getSession());
        //recojo la entidad principal actual
        $entidadPrincipal = $origenDatos->getAlineacionEntidad();
        //lanzo el proceso de actualización 
        [$form,$modoFormulario, $origenDatos] = ($alineacionDatosFormProcessor)($iddes, $origenDatos, $request);
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
            'urlMenu' =>  $this->urlMenu,
            'permisoEdicion' => $permisoEdicion,
            'entidadPrincipal' => $entidadPrincipal
           ]);        
        }
        return $this->render('alineacion/seleccion.html.twig', [
            'errorProceso' => $errorProceso,
            'locationAnterior' => $locationAnterior,
            'alineacion_form' => $form->createView(),
            'ClassBody' => $this->ClassBody,
            'urlCrear' =>  $this->urlCrear,
            'urlAyuda' =>  $this->urlAyuda,
            'urlMenu' =>  $this->urlMenu,
            'permisoEdicion' => $permisoEdicion,
            'entidadPrincipal' => $entidadPrincipal
        ]);
   }
}

