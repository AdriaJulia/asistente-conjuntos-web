<?php

namespace App\Controller;

use App\Enum\EstadoDescripcionDatosEnum;
use App\Service\Manager\OrigenDatosManager;
use App\Service\Manager\DescripcionDatosManager;
use App\Service\Processor\OrigenDatosFileFormProcessor;
use App\Service\Processor\OrigenDatosUrlFormProcessor;
use App\Service\Processor\OrigenDatosDataBaseFormProcessor;
use App\Enum\RutasAyudaEnum;
use App\Service\Controller\ToolController;
use App\Service\CurrentUser;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Psr\Log\LoggerInterface;

/*
 * Descripción: Es el controlador de todas la llamadas del paso 2, donde se crean y actualizan 
 *              los orígenes de datos a un a descripcion.
 *              Los orígenes pueden ser de fichero, url o base datos en cualquier formato.
 */
class OrigenDatosController extends AbstractController
{

    private $ClassBody = "asistente comunidad usuarioConectado";
    private $urlAyuda = "";
    private $urlCrear = "";
    private $urlMenu = "";

    /***
     * Descripcion: Crea, inserta un origen de datos por una url elegida en el formulario a una descripcion de datos dada por id
     *              La misma llamada es contralada para el test (comprobación), como para el guardado.
     * Parametros:
     *             iddes:                     id de la descripcion de los datos que se la va a insertar el origen
     *             origenDatosFormProcessor:  proceso back del origen de datos a una llamada
     *             origenDatosManager :       repositorio del origen de datos
     *             descripcionDatosManager :  repositorio de la descripcion de datos
     *             toolController:            clase de herramientas para procesoso comunes de los controladores
     *             request:                   El objeto request de la llamada
     */
     /**
     * @Route("/asistentecamposdatos/{iddes}/url/origen",  requirements={"iddes"="\d+"}, name="insert_asistentecamposdatos_url")
     */
    public function InsertActionUrl(int $iddes,
                                    OrigenDatosUrlFormProcessor $origenDatosFormProcessor,
                                    OrigenDatosManager $origenDatosManager,
                                    DescripcionDatosManager $descripcionDatosManager,
                                    LoggerInterface $logger,
                                    ToolController $toolController,
                                    Request $request) {
        $errorProceso = "";
        $origenDatos = $origenDatosManager->new();    
        $id=null;
        $Istest = true;
        //tomo la url para el botón anterior
        $locationAnterior = $this->generateUrl('update_asistentecamposdatos_paso3',["id"=>$iddes]);
        //tomo las urls del menu superior
        [$this->urlAyuda, $this->urlCrear, $this->urlMenu] = $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::ORIGEN_DATOS_URL,$this->getUser());
        [$form,$campos,$id,$Istest,$errorProceso]  = ($origenDatosFormProcessor)($iddes, $origenDatos, $request);
        if ($form->isSubmitted() && $form->isValid() && empty($errorProceso)) {
            //si es test devuelvo el resultado del test, si no redirijo al paso3
            if ($Istest) {
                $listaCampos = array();
                if (!empty($campos)) {
                    $listaCampos = explode(";",$campos);
                }
                if (!empty($errorProceso)) {
                    $errorProceso = str_replace("error_proceso","Error del proceso", $errorProceso);
                }
                return $this->render('descripcion/origen.html.twig', [
                    'errorProceso' => $errorProceso,
                    'locationAnterior' => $locationAnterior,
                    'locationSigiente' => "",
                    'campos' => $listaCampos,
                    'archivoActual' => "",
                    'ClassBody' => $this->ClassBody,
                    'urlCrear' =>  $this->urlCrear,
                    'urlAyuda' =>  $this->urlAyuda,
                    'urlMenu' =>  $this->urlMenu,
                    'permisoEdicion' => "block",
                    'origen_form' => $form->createView(),
                    'errors' => $form->getErrors()
                ]);
            } else {
                    return $this->redirectToRoute('insert_alineacion',["id"=>$id,"iddes"=>$iddes,"origen"=>"url"]); 
            }
        } else {
            $descripcionDatos = $descripcionDatosManager->find($iddes, $request->getSession());
            // solo se puede acceder si el estado es correcto y el usuario es el mismo que lo creó
            $permisoEdicion = $toolController->DamePermisoUsuarioActualEstado($descripcionDatos->getUsuario(), 
                                                                              $this->getUser(),
                                                                              $descripcionDatos->getEstado());
            return $this->render('descripcion/origen.html.twig', [
                'errorProceso' => $errorProceso,
                'locationAnterior' => $locationAnterior,
                'locationSigiente' => "",
                'campos' => $campos,
                'archivoActual' => "",
                'ClassBody' => $this->ClassBody,
                'urlCrear' =>  $this->urlCrear,
                'urlAyuda' =>  $this->urlAyuda,
                'urlMenu' =>  $this->urlMenu,
                'permisoEdicion' => $permisoEdicion,
                'origen_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }

    /***
     * Descripcion: Crea, inserta un origen de datos por un fichero elegida en el formulario a una descripcion de datos dada por id
     *              La misma llamada es contralada para el test (comprobación), como para el guardado.
     * Parametros:
     *             iddes:                     id de la descripcion de los datos que se la va a insertar el origen
     *             origenDatosFormProcessor:  proceso back del origen de datos a una llamada
     *             origenDatosManager :       repositorio del origen de datos
     *             descripcionDatosManager :  repositorio de la descripcion de datos
     *             toolController:            clase de herramientas para procesoso comunes de los controladores
     *             request:                   El objeto request de la llamada
     */
     /**
     * @Route("/asistentecamposdatos/{iddes}/file/origen",  requirements={"iddes"="\d+"}, name="insert_asistentecamposdatos_file")
     */
    public function InsertActionFile(int $iddes,
                                     OrigenDatosFileFormProcessor $origenDatosFormProcessor,
                                     OrigenDatosManager $origenDatosManager,
                                     DescripcionDatosManager $descripcionDatosManager,
                                     LoggerInterface $logger,
                                     ToolController $toolController,
                                     Request $request) {
        $errorProceso = "";
        $archivoActual = "";
        $origenDatos = $origenDatosManager->new();
         //tomo la url para el botón anterior
        $locationAnterior = $this->generateUrl('update_asistentecamposdatos_paso3',["id"=>$iddes]);
        $id=null;
        $Istest = true;
         //tomo las urls del menu superior
        [$this->urlAyuda, $this->urlCrear, $this->urlMenu] = $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::ORIGEN_DATOS_FILE,$this->getUser());
        [$form,$campos,$id, $Istest,  $archivoActual, $errorProceso] = ($origenDatosFormProcessor)($iddes,$origenDatos, $request);
        if ($form->isSubmitted() && $form->isValid() && empty($errorProceso)) {
            //si es test devuelvo el resultado del test, si no redirijo al paso3
            if ($Istest) {
                $listaCampos = array();
                if (!empty($campos)) {
                    $listaCampos = explode(";",$campos);
                }
                if (!empty($errorProceso)) {
                    $errorProceso = str_replace("error_proceso","Error del proceso", $errorProceso);
                }
                return $this->render('descripcion/origen.html.twig', [
                    'errorProceso' => $errorProceso,
                    'locationAnterior' => $locationAnterior,
                    'locationSigiente' => "",
                    'campos' => $listaCampos,
                    'archivoActual' =>  $archivoActual,
                    'ClassBody' => $this->ClassBody,
                    'urlCrear' =>  $this->urlCrear,
                    'urlAyuda' =>  $this->urlAyuda,
                    'urlMenu' =>  $this->urlMenu,
                    'permisoEdicion' => "block",
                    'origen_form' => $form->createView(),
                    'errors' => $form->getErrors()
                ]);
            } else {
                return $this->redirectToRoute('insert_alineacion',["id"=>$id,"iddes"=>$iddes,"origen"=>"file"]); 
            }
        } else {
            $descripcionDatos = $descripcionDatosManager->find($iddes, $request->getSession());
            // solo se puede acceder si el estado es correcto y el usuario es el mismo que lo creó
            $permisoEdicion = $toolController->DamePermisoUsuarioActualEstado($descripcionDatos->getUsuario(), 
                                                                              $this->getUser(),
                                                                              $descripcionDatos->getEstado());

            $locationSiguiente = "";
            return $this->render('descripcion/origen.html.twig', [
                'errorProceso' => $errorProceso,
                'locationAnterior' => $locationAnterior,
                'locationSigiente' => "",
                'campos' => $campos,
                'archivoActual' =>  $archivoActual,
                'ClassBody' => $this->ClassBody,
                'urlCrear' =>  $this->urlCrear,
                'urlAyuda' =>  $this->urlAyuda,
                'urlMenu' =>  $this->urlMenu,
                'permisoEdicion' => $permisoEdicion,
                'origen_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }

    /***
     * Descripcion: Crea, inserta un origen de datos por una base de datos elegida en el formulario a una descripcion de datos dada por id
     *              La misma llamada es contralada para el test (comprobación), como para el guardado.
     * Parametros:
     *             iddes:                     id de la descripcion de los datos que se la va a insertar el origen
     *             origenDatosFormProcessor:  proceso back del origen de datos a una llamada
     *             origenDatosManager :       repositorio del origen de datos
     *             descripcionDatosManager :  repositorio de la descripcion de datos
     *             toolController:            clase de herramientas para procesoso comunes de los controladores
     *             request:                   El objeto request de la llamada
     */
    /**
     * @Route("/asistentecamposdatos/{iddes}/database/origen",  requirements={"iddes"="\d+"}, name="insert_asistentecamposdatos_database")
     */
    public function InsertActionDataBase(int $iddes,
                                         OrigenDatosDataBaseFormProcessor $origenDatosFormProcessor,
                                         OrigenDatosManager $origenDatosManager,
                                         DescripcionDatosManager $descripcionDatosManager,
                                         LoggerInterface $logger,
                                         ToolController $toolController,
                                         Request $request) {
        $errorProceso = "";
        $origenDatos = $origenDatosManager->new();
         //tomo la url para el botón anterior
        $locationAnterior = $this->generateUrl('update_asistentecamposdatos_paso3',["id"=>$iddes]);
        $id=null;
        $Istest = true;
        //tomo las urls del menu superior
        [$this->urlAyuda, $this->urlCrear, $this->urlMenu] = $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::ORIGEN_DATOS_DB,$this->getUser());
        [$form,$campos,$id, $Istest, $errorProceso] = ($origenDatosFormProcessor)($iddes, $origenDatos, $request);
        if ($form->isSubmitted() && $form->isValid() && empty($errorProceso)) {
            //si es test devuelvo el resultado del test, si no redirijo al paso3
             if ($Istest) {
                $listaCampos = array();
                if (!empty($campos)) {
                    $listaCampos = explode(";",$campos);
                }
                if (!empty($errorProceso)) {
                    $errorProceso = str_replace("error_proceso","Error del proceso", $errorProceso);
                }
                return $this->render('descripcion/origen.html.twig', [
                    'errorProceso' => $errorProceso,
                    'locationAnterior' => $locationAnterior,
                    'locationSigiente' => "",
                    'campos' => $listaCampos,
                    'archivoActual' => "",
                    'ClassBody' => $this->ClassBody,
                    'urlCrear' =>  $this->urlCrear,
                    'urlAyuda' =>  $this->urlAyuda,
                    'urlMenu' =>  $this->urlMenu,
                    'permisoEdicion' => "block",
                    'origen_form' => $form->createView(),
                    'errors' => $form->getErrors()
                ]);
            } else {
                return $this->redirectToRoute('insert_alineacion',["id"=>$id,"iddes"=>$iddes,"origen"=>"database"]); 
            }
        } else {
            $descripcionDatos = $descripcionDatosManager->find($iddes, $request->getSession());
            // solo se puede acceder si el estado es correcto y el usuario es el mismo que lo creó
            $permisoEdicion = $toolController->DamePermisoUsuarioActualEstado($descripcionDatos->getUsuario(), 
                                                                              $this->getUser(),
                                                                              $descripcionDatos->getEstado());
            $locationSiguiente = "";
            return $this->render('descripcion/origen.html.twig', [
                'errorProceso' => $errorProceso,
                'locationAnterior' => $locationAnterior,
                'locationSigiente' => "",
                'campos' => $campos,
                'archivoActual' => "",
                'ClassBody' => $this->ClassBody,
                'urlCrear' =>  $this->urlCrear,
                'urlAyuda' =>  $this->urlAyuda,
                'urlMenu' =>  $this->urlMenu,
                'permisoEdicion' => $permisoEdicion,
                'origen_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }

    /***
     * Descripcion: Actualiza un origen de datos por una url elegida en el formulario a una descripcion de datos dada por id.
     *              La misma llamada es contralada para el test (comprobación), como para el guardado.
     * Parametros:
     *             id:                        id del origen de dartos a actualizar
     *             iddes:                     id de la descripcion de los datos que se la va a insertar el origen
     *             origenDatosFormProcessor:  proceso back del origen de datos a una llamada
     *             origenDatosManager :       repositorio del origen de datos
     *             descripcionDatosManager :  repositorio de la descripcion de datos
     *             toolController:            clase de herramientas para procesoso comunes de los controladores
     *             request:                   El objeto request de la llamada
     */
     /**
     * @Route("/asistentecamposdatos/{iddes}/url/origen/{id}", requirements={"id"="\d+", "iddes"="\d+"}, name="update_asistentecamposdatos_url")
     */
    public function UpdateActionUrl(int $id,
                                    int $iddes,
                                    OrigenDatosUrlFormProcessor $origenDatosFormProcessor,
                                    OrigenDatosManager $origenDatosManager,
                                    DescripcionDatosManager $descripcionDatosManager,
                                    LoggerInterface $logger,
                                    ToolController $toolController,
                                    Request $request) {

        $errorProceso = "";
        //tomo el objeto origendatos existente en la descripcion
        $origenDatos = $origenDatosManager->find($id, $request->getSession());
         //tomo la url para el botón anterior
        $locationAnterior = $this->generateUrl('update_asistentecamposdatos_paso3',["id"=>$iddes]);
        $id=null;
        $Istest = true;
        //tomo las urls del menu superior
        [$this->urlAyuda, $this->urlCrear, $this->urlMenu] = $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::ORIGEN_DATOS_URL,$this->getUser());
        [$form,$campos,$id, $Istest, $errorProceso] = ($origenDatosFormProcessor)($iddes, $origenDatos, $request);
        if ($form->isSubmitted() && $form->isValid() && empty($errorProceso)) {
            //si es test devuelvo el resultado del test, si no redirijo al paso3
            if ($Istest) {
                $listaCampos = array();
                if (!empty($campos)) {
                    $listaCampos = explode(";",$campos);
                }
                if (!empty($errorProceso)) {
                    $errorProceso = str_replace("error_proceso","Error del proceso", $errorProceso);
                }
                return $this->render('descripcion/origen.html.twig', [
                    'errorProceso' => $errorProceso,
                    'locationAnterior' => $locationAnterior,
                    'locationSigiente' => "",
                    'campos' => $listaCampos,
                    'archivoActual' => "",
                    'ClassBody' => $this->ClassBody,
                    'urlCrear' =>  $this->urlCrear,
                    'urlAyuda' =>  $this->urlAyuda,
                    'urlMenu' =>  $this->urlMenu,
                    'permisoEdicion' => "block",
                    'origen_form' => $form->createView(),
                    'errors' => $form->getErrors()
                ]);
            } else {
                return $this->redirectToRoute('insert_alineacion',["id"=>$id,"iddes"=>$iddes,"origen"=>"url"]); 
            }
        } else {
            $descripcionDatos = $descripcionDatosManager->find($iddes, $request->getSession());
            // solo se puede acceder si el estado es correcto y el usuario es el mismo que lo creó
            $permisoEdicion = $toolController->DamePermisoUsuarioActualEstado($descripcionDatos->getUsuario(), 
                                                                              $this->getUser(),
                                                                              $descripcionDatos->getEstado()); 
            $locationSiguiente = "";
            return $this->render('descripcion/origen.html.twig', [
                'errorProceso' => $errorProceso,
                'locationAnterior' => $locationAnterior,
                'locationSigiente' => "",
                'campos' => $campos,
                'archivoActual' => "",
                'ClassBody' => $this->ClassBody,
                'urlCrear' =>  $this->urlCrear,
                'urlAyuda' =>  $this->urlAyuda,
                'urlMenu' =>  $this->urlMenu,
                'permisoEdicion' => $permisoEdicion,
                'origen_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }


    /***
     * Descripcion: Actualiza un origen de datos por una archivo elegida en el formulario a una descripcion de datos dada por id
     *              La misma llamada es contralada para el test (comprobación), como para el guardado.
     * Parametros:
     *             id:                        id del origen de dartos a actualizar
     *             iddes:                     id de la descripcion de los datos que se la va a insertar el origen
     *             origenDatosFormProcessor:  proceso back del origen de datos a una llamada
     *             origenDatosManager :       repositorio del origen de datos
     *             descripcionDatosManager :  repositorio de la descripcion de datos
     *             toolController:            clase de herramientas para procesoso comunes de los controladores
     *             request:                   El objeto request de la llamada
     */
     /**
     * @Route("/asistentecamposdatos/{iddes}/file/origen/{id}", requirements={"id"="\d+", "iddes"="\d+"}, name="update_asistentecamposdatos_file")
     */
    public function UpdateActionFile(int $id,
                                     int $iddes,
                                     OrigenDatosFileFormProcessor $origenDatosFormProcessor,
                                     OrigenDatosManager $origenDatosManager,
                                     DescripcionDatosManager $descripcionDatosManager,
                                     LoggerInterface $logger,
                                     ToolController $toolController,
                                     Request $request) {

        $errorProceso = "";
        $archivoActual = "";
         //tomo el objeto origendatos existente en la descripcion
        $origenDatos = $origenDatosManager->find($id, $request->getSession());
         //tomo la url para el botón anterior
        $locationAnterior = $this->generateUrl('update_asistentecamposdatos_paso3',["id"=>$iddes]);
        $id=null;
        $Istest = true;
       //tomo las urls del menu superior
        [$this->urlAyuda, $this->urlCrear, $this->urlMenu] = $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::ORIGEN_DATOS_FILE,$this->getUser());
        [$form,$campos,$id, $Istest,  $archivoActual, $errorProceso] = ($origenDatosFormProcessor)($iddes, $origenDatos, $request);
        if ($form->isSubmitted() && $form->isValid() && empty($errorProceso)) {
        //si es test devuelvo el resultado del test, si no redirijo al paso3
             if ($Istest) {
                $listaCampos = array();
                if (!empty($campos)) {
                    $listaCampos = explode(";",$campos);
                }
                if (!empty($errorProceso)) {
                    $errorProceso = str_replace("error_proceso","Error del proceso", $errorProceso);
                }
                return $this->render('descripcion/origen.html.twig', [
                    'errorProceso' => $errorProceso,
                    'locationAnterior' => $locationAnterior,
                    'locationSigiente' => "",
                    'archivoActual' => $archivoActual,
                    'campos' => $listaCampos,
                    'ClassBody' => $this->ClassBody,
                    'urlCrear' =>  $this->urlCrear,
                    'urlAyuda' =>  $this->urlAyuda,
                    'urlMenu' =>  $this->urlMenu,
                    'permisoEdicion' => "block",
                    'origen_form' => $form->createView(),
                    'errors' => $form->getErrors()
                ]);
            } else {
                return $this->redirectToRoute('insert_alineacion',["id"=>$id,"iddes"=>$iddes,"origen"=>"file"]); 
            }
        } else {
            $descripcionDatos = $descripcionDatosManager->find($iddes, $request->getSession());
            // solo se puede acceder si el estado es correcto y el usuario es el mismo que lo creó
            $permisoEdicion = $toolController->DamePermisoUsuarioActualEstado($descripcionDatos->getUsuario(), 
                                                                              $this->getUser(),
                                                                              $descripcionDatos->getEstado()); 
            $locationSiguiente = "";
            return $this->render('descripcion/origen.html.twig', [
                'errorProceso' => $errorProceso,
                'locationAnterior' => $locationAnterior,
                'locationSigiente' => "",
                'campos' => $campos,
                'archivoActual' => $archivoActual,
                'ClassBody' => $this->ClassBody,
                'urlCrear' =>  $this->urlCrear,
                'urlAyuda' =>  $this->urlAyuda,
                'urlMenu' =>  $this->urlMenu,
                'permisoEdicion' => $permisoEdicion,
                'origen_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }

    /***
     * Descripcion: Actualiza un origen de datos por una base de datos elegida en el formulario a una descripcion de datos dada por id
     *              La misma llamada es contralada para el test (comprobación), como para el guardado.
     * Parametros:
     *             id:                        id del origen de dartos a actualizar
     *             iddes:                     id de la descripcion de los datos que se la va a insertar el origen
     *             origenDatosFormProcessor:  proceso back del origen de datos a una llamada
     *             origenDatosManager :       repositorio del origen de datos
     *             descripcionDatosManager :  repositorio de la descripcion de datos
     *             toolController:            clase de herramientas para procesoso comunes de los controladores
     *             request:                   El objeto request de la llamada
     */
    /**
     * @Route("/asistentecamposdatos/{iddes}/database/origen/{id}", requirements={"id"="\d+", "iddes"="\d+"}, name="update_asistentecamposdatos_database")
     */
    public function UpdateActionDataBase(int $id,
                                         int $iddes,
                                         OrigenDatosDataBaseFormProcessor $origenDatosFormProcessor,
                                         OrigenDatosManager $origenDatosManager,
                                         DescripcionDatosManager $descripcionDatosManager,
                                         LoggerInterface $logger,
                                         ToolController $toolController,
                                         Request $request) {

        $errorProceso = "";
        //tomo el objeto origendatos existente en la descripcion
        $origenDatos = $origenDatosManager->find($id, $request->getSession());
         //tomo la url para el botón anterior
        $locationAnterior = $this->generateUrl('update_asistentecamposdatos_paso3',["id"=>$iddes]);
        $id=null;
        $Istest = true;
        //tomo las urls del menu superior
        [$this->urlAyuda, $this->urlCrear, $this->urlMenu] = $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::ORIGEN_DATOS_DB,$this->getUser());
        [$form,$campos,$id, $Istest, $errorProceso] = ($origenDatosFormProcessor)($iddes, $origenDatos, $request);
        if ($form->isSubmitted() && $form->isValid() && empty($errorProceso)) {
            //si es test devuelvo el resultado del test, si no redirijo al paso3
            if ($Istest) {
                $listaCampos = array();
                if (!empty($campos)) {
                    $listaCampos = explode(";",$campos);
                }
                if (!empty($errorProceso)) {
                    $errorProceso = str_replace("error_proceso","Error del proceso", $errorProceso);
                }
                return $this->render('descripcion/origen.html.twig', [
                    'errorProceso' => $errorProceso,
                    'locationAnterior' => $locationAnterior,
                    'locationSigiente' => "",
                    'campos' => $listaCampos,
                    'archivoActual' => "",
                    'ClassBody' => $this->ClassBody,
                    'urlCrear' =>  $this->urlCrear,
                    'urlAyuda' =>  $this->urlAyuda,
                    'urlMenu' =>  $this->urlMenu,
                    'permisoEdicion' => "block",
                    'origen_form' => $form->createView(),
                    'errors' => $form->getErrors()
                ]);
            } else {
                return $this->redirectToRoute('insert_alineacion',["id"=>$id,"iddes"=>$iddes,"origen"=>"database"]); 
            }
        } else {
            $descripcionDatos = $descripcionDatosManager->find($iddes, $request->getSession());
            // solo se puede acceder si el estado es correcto y el usuario es el mismo que lo creó
            $permisoEdicion = $toolController->DamePermisoUsuarioActualEstado($descripcionDatos->getUsuario(), 
                                                                              $this->getUser(),
                                                                              $descripcionDatos->getEstado()); 
            $locationSiguiente = "";
            return $this->render('descripcion/origen.html.twig', [
                'errorProceso' => $errorProceso,
                'locationAnterior' => $locationAnterior,
                'locationSigiente' => "",
                'campos' => $campos,
                'archivoActual' => "",
                'ClassBody' => $this->ClassBody,
                'urlCrear' =>  $this->urlCrear,
                'urlAyuda' =>  $this->urlAyuda,
                'urlMenu' =>  $this->urlMenu,
                'permisoEdicion' => $permisoEdicion,
                'origen_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }
  }
