<?php

namespace App\Controller;

use App\Service\Manager\OrigenDatosManager;
use App\Service\Manager\DescripcionDatosManager;
use App\Service\Processor\OrigenDatosFileFormProcessor;
use App\Service\Processor\OrigenDatosUrlFormProcessor;
use App\Service\Processor\OrigenDatosDataBaseFormProcessor;
use App\Enum\RutasAyudaEnum;
use App\Enum\TipoAlineacionEnum;
use App\enum\TipoOrigenDatosEnum;
use App\Service\Controller\ToolController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Psr\Log\LoggerInterface;

/*
 * Descripción: Es el controlador de todas la llamadas del paso 2, donde se crean y actualizan 
 *              el origen de datos de una distribución. 
 *              Los orígenes pueden ser de fichero, url (xml, json, csv, xsl, xlsx) o base datos.
 */
class OrigenDatosController extends AbstractController
{

    private $ClassBody = "asistente comunidad usuarioConectado";
    private $urlAyuda = "";
    private $urlSoporte = "";
    private $urlCrear = "";
    private $urlMenu = "";

    /***
     * Descripción: Crea, inserta un origen de datos por una url elegida en el formulario a una descripción de datos dada por id
     *              La misma llamada es contralada para el test (comprobación), como para el guardado.
     * Parámetros:
     *             iddes:                     id de la descripción de los datos que se la va a insertar el origen
     *             origenDatosFormProcessor:  proceso back del origen de datos a una llamada
     *             origenDatosManager :       repositorio del origen de datos
     *             descripcionDatosManager :  repositorio de la descripción de datos
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
                                    ToolController $toolController,
                                    Request $request) {
        $errorProceso = "";
        $camposDistintos  = "";
        $camposAlineados = "";
        $camposActuales = "";
        $origenDatos = $origenDatosManager->new();    
        $id=null;
        $Istest = true;
        $muestraSiguiente = "";
        $locationAnterior = "";
        $existe = false;
        //tomo las urls del menu superior
        [$this->urlAyuda, $this->urlSoporte, $this->urlCrear, $this->urlMenu] = $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::ORIGEN_DATOS_URL,$this->getUser());
        [$form,$campos,$id,$Istest,$errorProceso]  = ($origenDatosFormProcessor)($iddes, $origenDatos, $request);
        if (!empty($campos)) {
            $muestraSiguiente = "muestraSiguiente";
        } 
         //tomo la url para el botón anterior
        if ($descripcionDatosManager->find($iddes,$request->getSession())->getDistribucion()>0) {
            $locationAnterior ="";
        } else {
            $locationAnterior = $this->generateUrl('update_asistentecamposdatos_paso2',["id"=>$iddes]);
        }
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
                    'muestraSiguiente' => $muestraSiguiente,
                    'campos' => $listaCampos,
                    'archivoActual' => "",
                    'ClassBody' => $this->ClassBody,
                    'urlCrear' =>  $this->urlCrear,
                    'urlAyuda' =>  $this->urlAyuda,
                    'urlSoporte' =>  $this->urlSoporte,
                    'urlMenu' =>  $this->urlMenu,
                    'camposDistintos' => $camposDistintos,
                    'camposAlineados' => $camposAlineados,
                    'camposActuales' => $camposActuales,
                    'permisoEdicion' => "block",
                    'existe' => $existe,
                    'origen_form' => $form->createView(),
                    'errors' => $form->getErrors()
                ]);
            } else {
                $locationSiguiente = $locationSiguiente =  $toolController->DameSiguienteAlineacion(TipoAlineacionEnum::CAMPOS, $iddes, $id,TipoOrigenDatosEnum::URL);                         
                return $this->redirect($locationSiguiente);
            }
        } else {
            $descripcionDatos = $descripcionDatosManager->find($iddes, $request->getSession());
            //si es hijo quito el enlace a anterior
            if ($descripcionDatos->getDistribucion()>0) {
                $locationAnterior ="";
            }
            // solo se puede acceder si el estado es correcto y el usuario es el mismo que lo creó
            $permisoEdicion = $toolController->DamePermisoUsuarioActualEstado($descripcionDatos->getUsuario(), 
                                                                              $this->getUser(),
                                                                              $descripcionDatos->getEstado());
            return $this->render('descripcion/origen.html.twig', [
                'errorProceso' => $errorProceso,
                'locationAnterior' => $locationAnterior,
                'locationSigiente' => "",
                'muestraSiguiente' => $muestraSiguiente, 
                'campos' => $campos,
                'archivoActual' => "",
                'ClassBody' => $this->ClassBody,
                'urlCrear' =>  $this->urlCrear,
                'urlAyuda' =>  $this->urlAyuda,
                'urlSoporte' =>  $this->urlSoporte,
                'urlMenu' =>  $this->urlMenu,
                'camposDistintos' => $camposDistintos,
                'camposAlineados' => $camposAlineados,
                'camposActuales' => $camposActuales,
                'permisoEdicion' => $permisoEdicion,
                'existe' => $existe,
                'origen_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }

    /***
     * Descripción: Crea, inserta un origen de datos por un fichero elegida en el formulario a una descripcion de datos dada por id
     *              La misma llamada es contralada para el test (comprobación), como para el guardado.
     * Parámetros:
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
                                     ToolController $toolController,
                                     Request $request) {
        $errorProceso = "";
        $camposDistintos  = "";
        $camposAlineados = "";
        $camposActuales = "";
        $archivoActual = "";
        $origenDatos = $origenDatosManager->new();
        $existe = false;

        $id=null;
        $Istest = true;
        $muestraSiguiente = "";
         //tomo las urls del menu superior
        [$this->urlAyuda, $this->urlSoporte, $this->urlCrear, $this->urlMenu] = $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::ORIGEN_DATOS_FILE,$this->getUser());
        [$form,$campos,$id, $Istest,  $archivoActual, $existe, $errorProceso] = ($origenDatosFormProcessor)($iddes,$origenDatos, $request);

        if (!empty($archivoActual)) {
            if (!empty($request->getSession()->get('NombreOriginalFile'))) {
                $archivoActual = $request->getSession()->get('NombreOriginalFile');
            } else {
                $archivoActual = basename($archivoActual);
            }
        }
        if (!empty($campos)) {
            $muestraSiguiente = "muestraSiguiente";
        } 
         //tomo la url para el botón anterior
        if ($descripcionDatosManager->find($iddes,$request->getSession())->getDistribucion()>0) {
            $locationAnterior ="";
        } else {
            $locationAnterior = $this->generateUrl('update_asistentecamposdatos_paso2',["id"=>$iddes]);
        }
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
                    'muestraSiguiente' => $muestraSiguiente, 
                    'campos' => $listaCampos,
                    'archivoActual' =>  $archivoActual,
                    'ClassBody' => $this->ClassBody,
                    'urlCrear' =>  $this->urlCrear,
                    'urlAyuda' =>  $this->urlAyuda,
                    'urlSoporte' =>  $this->urlSoporte,
                    'urlMenu' =>  $this->urlMenu,
                    'camposDistintos' => $camposDistintos,
                    'camposAlineados' => $camposAlineados,
                    'camposActuales' => $camposActuales,
                    'permisoEdicion' => "block",
                    'existe' => $existe,
                    'origen_form' => $form->createView(),
                    'errors' => $form->getErrors()
                ]);
            } else {
                $locationSiguiente = $locationSiguiente =  $toolController->DameSiguienteAlineacion(TipoAlineacionEnum::CAMPOS, $iddes, $id,TipoOrigenDatosEnum::ARCHIVO);                 
                return $this->redirect($locationSiguiente);
            }
        } else {
            $descripcionDatos = $descripcionDatosManager->find($iddes, $request->getSession());
              //si es hijo quito el enlace a anterior
            if ($descripcionDatos->getDistribucion()>0) {
                $locationAnterior ="";
            }
            // solo se puede acceder si el estado es correcto y el usuario es el mismo que lo creó
            $permisoEdicion = $toolController->DamePermisoUsuarioActualEstado($descripcionDatos->getUsuario(), 
                                                                              $this->getUser(),
                                                                              $descripcionDatos->getEstado());

            $locationSiguiente = "";
            return $this->render('descripcion/origen.html.twig', [
                'errorProceso' => $errorProceso,
                'locationAnterior' => $locationAnterior,
                'locationSigiente' => "",
                'muestraSiguiente' => $muestraSiguiente, 
                'campos' => $campos,
                'archivoActual' =>  $archivoActual,
                'ClassBody' => $this->ClassBody,
                'urlCrear' =>  $this->urlCrear,
                'urlAyuda' =>  $this->urlAyuda,
                'urlSoporte' =>  $this->urlSoporte,
                'urlMenu' =>  $this->urlMenu,
                'camposDistintos' => $camposDistintos,
                'camposAlineados' => $camposAlineados,
                'camposActuales' => $camposActuales,
                'permisoEdicion' => $permisoEdicion,
                'existe' => $existe,
                'origen_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }

    /***
     * Descripción: Crea, inserta un origen de datos por una base de datos elegida en el formulario a una descripcion de datos dada por id
     *              La misma llamada es contralada para el test (comprobación), como para el guardado.
     * Parámetros:
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
                                         ToolController $toolController,
                                         Request $request) {
        $errorProceso = "";
        $camposDistintos  = "";
        $camposAlineados = "";
        $camposActuales = "";
        $origenDatos = $origenDatosManager->new();
        $id=null;
        $Istest = true;
        $muestraSiguiente = "";
        $existe = false;
        //tomo las urls del menu superior
        [$this->urlAyuda, $this->urlSoporte, $this->urlCrear, $this->urlMenu] = $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::ORIGEN_DATOS_DB,$this->getUser());
        [$form,$campos,$id, $Istest, $errorProceso] = ($origenDatosFormProcessor)($iddes, $origenDatos, $request);
        if (!empty($campos)) {
            $muestraSiguiente = "muestraSiguiente";
        } 
         //tomo la url para el botón anterior
         if ($descripcionDatosManager->find($iddes,$request->getSession())->getDistribucion()>0) {
            $locationAnterior ="";
        } else {
            $locationAnterior = $this->generateUrl('update_asistentecamposdatos_paso2',["id"=>$iddes]);
        }
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
                    'muestraSiguiente' => $muestraSiguiente,
                    'campos' => $listaCampos,
                    'archivoActual' => "",
                    'ClassBody' => $this->ClassBody,
                    'urlCrear' =>  $this->urlCrear,
                    'urlAyuda' =>  $this->urlAyuda,
                    'urlSoporte' =>  $this->urlSoporte,
                    'urlMenu' =>  $this->urlMenu,
                    'camposDistintos' => $camposDistintos,
                    'camposAlineados' => $camposAlineados,
                    'camposActuales' => $camposActuales,
                    'permisoEdicion' => "block",
                    'existe' => $existe,
                    'origen_form' => $form->createView(),
                    'errors' => $form->getErrors()
                ]);
            } else {
                $locationSiguiente = $locationSiguiente =  $toolController->DameSiguienteAlineacion(TipoAlineacionEnum::CAMPOS, $iddes, $id,TipoOrigenDatosEnum::BASEDATOS);            
                return $this->redirect($locationSiguiente);
            }
        } else {
            $descripcionDatos = $descripcionDatosManager->find($iddes, $request->getSession());
              //si es hijo quito el enlace a anterior
            if ($descripcionDatos->getDistribucion()>0) {
                $locationAnterior ="";
            }
            // solo se puede acceder si el estado es correcto y el usuario es el mismo que lo creó
            $permisoEdicion = $toolController->DamePermisoUsuarioActualEstado($descripcionDatos->getUsuario(), 
                                                                              $this->getUser(),
                                                                              $descripcionDatos->getEstado());
            $locationSiguiente = "";
            return $this->render('descripcion/origen.html.twig', [
                'errorProceso' => $errorProceso,
                'locationAnterior' => $locationAnterior,
                'locationSigiente' => "",
                'muestraSiguiente' => $muestraSiguiente,
                'campos' => $campos,
                'archivoActual' => "",
                'ClassBody' => $this->ClassBody,
                'urlCrear' =>  $this->urlCrear,
                'urlAyuda' =>  $this->urlAyuda,
                'urlSoporte' =>  $this->urlSoporte,
                'urlMenu' =>  $this->urlMenu,
                'camposDistintos' => $camposDistintos,
                'camposAlineados' => $camposAlineados,
                'camposActuales' => $camposActuales,
                'permisoEdicion' => $permisoEdicion,
                'existe' => $existe,
                'origen_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }

    /***
     * Descripción: Actualiza un origen de datos por una url elegida en el formulario a una descripcion de datos dada por id.
     *              La misma llamada es contralada para el test (comprobación), como para el guardado.
     * Parámetros:
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
                                    ToolController $toolController,
                                    Request $request) {

        $errorProceso = "";
        $camposDistintos  = "";
        $camposAlineados = "";
        $camposActuales = "";
        $existe = false;
        //tomo el objeto origendatos existente en la descripcion
        $origenDatos = $origenDatosManager->find($id, $request->getSession());

        //tomo los campos alineados del regitro actual
        $camposAlineados = (!empty($origenDatos->getAlineacionRelaciones()))  ? get_object_vars(json_decode(str_replace(",}","}",$origenDatos->getAlineacionRelaciones()))) : array();

        $id=null;
        $Istest = true;
        $muestraSiguiente ="";
        //tomo las urls del menu superior
        [$this->urlAyuda, $this->urlSoporte, $this->urlCrear, $this->urlMenu] = $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::ORIGEN_DATOS_URL,$this->getUser());
        [$form,$campos,$id, $Istest, $errorProceso] = ($origenDatosFormProcessor)($iddes, $origenDatos, $request);
        //compruebo que los nuevo campos estén en los campos ya alineados
        [$camposActuales ,$camposDistintos,$camposAlineados]= $toolController->getOntologiasAlienedas($campos,$camposAlineados);
        if (!empty($campos)) {
            $muestraSiguiente = "muestraSiguiente";
        } 
        //tomo la url para el botón anterior
        if ($descripcionDatosManager->find($iddes,$request->getSession())->getDistribucion()>0) {
            $locationAnterior ="";
        } else {
            $locationAnterior = $this->generateUrl('update_asistentecamposdatos_paso2',["id"=>$iddes]);
        }
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
                    'muestraSiguiente' => $muestraSiguiente,
                    'campos' => $listaCampos,
                    'archivoActual' => "",
                    'ClassBody' => $this->ClassBody,
                    'urlCrear' =>  $this->urlCrear,
                    'urlAyuda' =>  $this->urlAyuda,
                    'urlSoporte' =>  $this->urlSoporte,
                    'urlMenu' =>  $this->urlMenu,
                    'camposDistintos' => $camposDistintos,
                    'camposAlineados' => $camposAlineados,
                    'camposActuales' => $camposActuales,
                    'permisoEdicion' => "block",
                    'existe' => $existe,
                    'origen_form' => $form->createView(),
                    'errors' => $form->getErrors()
                ]);
            } else {
                $alineacion =  isset($origenDatos) ? $origenDatos->getTipoAlineacion() : TipoAlineacionEnum::CAMPOS;
                $locationSiguiente = $locationSiguiente =  $toolController->DameSiguienteAlineacion($alineacion, $iddes, $id,TipoOrigenDatosEnum::URL);       
                return $this->redirect($locationSiguiente);
            }
        } else {
            $descripcionDatos = $descripcionDatosManager->find($iddes, $request->getSession());
              //si es hijo quito el enlace a anterior
            if ($descripcionDatos->getDistribucion()>0) {
                $locationAnterior ="";
            }
            // solo se puede acceder si el estado es correcto y el usuario es el mismo que lo creó
            $permisoEdicion = $toolController->DamePermisoUsuarioActualEstado($descripcionDatos->getUsuario(), 
                                                                              $this->getUser(),
                                                                              $descripcionDatos->getEstado()); 
            $locationSiguiente = "";
            return $this->render('descripcion/origen.html.twig', [
                'errorProceso' => $errorProceso,
                'locationAnterior' => $locationAnterior,
                'locationSigiente' => "",
                'muestraSiguiente' => $muestraSiguiente,
                'campos' => $campos,
                'archivoActual' => "",
                'ClassBody' => $this->ClassBody,
                'urlCrear' =>  $this->urlCrear,
                'urlAyuda' =>  $this->urlAyuda,
                'urlSoporte' =>  $this->urlSoporte,
                'urlMenu' =>  $this->urlMenu,
                'camposDistintos' => $camposDistintos,
                'camposAlineados' => $camposAlineados,
                'camposActuales' => $camposActuales,
                'permisoEdicion' => $permisoEdicion,
                'existe' => $existe,
                'origen_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }


    /***
     * DescripciónActualiza un origen de datos por una archivo elegida en el formulario a una descripcion de datos dada por id
     *              La misma llamada es contralada para el test (comprobación), como para el guardado.
     * Parámetros:
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
        $camposDistintos  = "";
        $camposAlineados = "";
        $camposActuales = "";
        $existe = false;
         //tomo el objeto origendatos existente en la descripcion
        $origenDatos = $origenDatosManager->find($id, $request->getSession());

        //tomo los campos alineados del regitro actual
        $camposAlineados = (!empty($origenDatos->getAlineacionRelaciones()))  ? get_object_vars(json_decode(str_replace(",}","}",$origenDatos->getAlineacionRelaciones()))) : array();
        $id=null;
        $Istest = true;
        $muestraSiguiente = null;
       //tomo las urls del menu superior
               [$this->urlAyuda, $this->urlSoporte, $this->urlCrear, $this->urlMenu] = $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::ORIGEN_DATOS_FILE,$this->getUser());

        [$form,$campos,$id, $Istest,  $archivoActual, $existe, $errorProceso] = ($origenDatosFormProcessor)($iddes, $origenDatos, $request);
        if (!empty($archivoActual)) {
            if (!empty($request->getSession()->get('NombreOriginalFile'))) {
                $archivoActual = $request->getSession()->get('NombreOriginalFile');
            } else {
                $archivoActual = basename($archivoActual);
            }
        }
        //compruebo que los nuevo campos esten en los campos ya alineados
        [$camposActuales ,$camposDistintos,$camposAlineados] = $toolController->getOntologiasAlienedas($campos,$camposAlineados);
        if (!empty($archivoActual) || !empty($campos)) {
            $muestraSiguiente = "muestraSiguiente";
        } 
        //tomo la url para el botón anterior
        if ($descripcionDatosManager->find($iddes,$request->getSession())->getDistribucion()>0) {
            $locationAnterior ="";
        } else {
            $locationAnterior = $this->generateUrl('update_asistentecamposdatos_paso2',["id"=>$iddes]);
        }
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
                    'muestraSiguiente' => $muestraSiguiente, 
                    'ClassBody' => $this->ClassBody,
                    'urlCrear' =>  $this->urlCrear,
                    'urlAyuda' =>  $this->urlAyuda,
                    'urlSoporte' =>  $this->urlSoporte,
                    'urlMenu' =>  $this->urlMenu,
                    'camposDistintos' => $camposDistintos,
                    'camposAlineados' => $camposAlineados,
                    'camposActuales' => $camposActuales,
                    'permisoEdicion' => "block",
                    'existe' => $existe,
                    'origen_form' => $form->createView(),
                    'errors' => $form->getErrors()
                ]);
            } else {
                $alineacion =  isset($origenDatos) ? $origenDatos->getTipoAlineacion() : TipoAlineacionEnum::CAMPOS;
                $locationSiguiente = $locationSiguiente =  $toolController->DameSiguienteAlineacion($alineacion, $iddes, $id,TipoOrigenDatosEnum::ARCHIVO);       
                return $this->redirect($locationSiguiente);
            }
        } else {
            $descripcionDatos = $descripcionDatosManager->find($iddes, $request->getSession());
              //si es hijo quito el enlace a anterior
            if ($descripcionDatos->getDistribucion()>0) {
                $locationAnterior ="";
            }
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
                'muestraSiguiente' => $muestraSiguiente, 
                'archivoActual' => $archivoActual,
                'ClassBody' => $this->ClassBody,
                'urlCrear' =>  $this->urlCrear,
                'urlAyuda' =>  $this->urlAyuda,
                'urlSoporte' =>  $this->urlSoporte,
                'urlMenu' =>  $this->urlMenu,
                'camposDistintos' => $camposDistintos,
                'camposAlineados' => $camposAlineados,
                'camposActuales' => $camposActuales,
                'permisoEdicion' => $permisoEdicion,
                'existe' => $existe,
                'origen_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }

    /***
     * DescripciónActualiza un origen de datos por una base de datos elegida en el formulario a una descripcion de datos dada por id
     *              La misma llamada es contralada para el test (comprobación), como para el guardado.
     * Parámetros:
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
        $camposDistintos  = "";
        $camposAlineados = "";
        $camposActuales = "";
        $existe = false;
        
        //tomo el objeto origendatos existente en la descripcion
        $origenDatos = $origenDatosManager->find($id, $request->getSession());

        //tomo los campos alineados del regitro actual
        $camposAlineados = (!empty($origenDatos->getAlineacionRelaciones()))  ? get_object_vars(json_decode(str_replace(",}","}",$origenDatos->getAlineacionRelaciones()))) : array();
        $id=null;
        $Istest = true;
        $muestraSiguiente = "";
        //tomo las urls del menu superior
        [$this->urlAyuda, $this->urlSoporte, $this->urlCrear, $this->urlMenu] = $toolController->getAyudaCrearMenu($_SERVER,RutasAyudaEnum::ORIGEN_DATOS_DB,$this->getUser());
        [$form,$campos,$id, $Istest, $errorProceso] = ($origenDatosFormProcessor)($iddes, $origenDatos, $request);

        //compruebo que los nuevo campos estén en los campos ya alineados
        [$camposActuales ,$camposDistintos,$camposAlineados]= $toolController->getOntologiasAlienedas($campos,$camposAlineados);
        if (!empty($campos)) {
            $muestraSiguiente = "muestraSiguiente";
        } 
        //tomo la url para el botón anterior
        if ($descripcionDatosManager->find($iddes,$request->getSession())->getDistribucion()>0) {
            $locationAnterior ="";
        } else {
            $locationAnterior = $this->generateUrl('update_asistentecamposdatos_paso2',["id"=>$iddes]);
        }
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
                    'muestraSiguiente' => $muestraSiguiente, 
                    'archivoActual' => "",
                    'ClassBody' => $this->ClassBody,
                    'urlCrear' =>  $this->urlCrear,
                    'urlAyuda' =>  $this->urlAyuda,
                    'urlSoporte' =>  $this->urlSoporte,
                    'urlMenu' =>  $this->urlMenu,
                    'camposDistintos' => $camposDistintos,
                    'camposAlineados' => $camposAlineados,
                    'camposActuales' => $camposActuales,
                    'permisoEdicion' => "block",
                    'existe' => $existe,
                    'origen_form' => $form->createView(),
                    'errors' => $form->getErrors()
                ]);
            } else {
                $alineacion =  isset($origenDatos) ? $origenDatos->getTipoAlineacion() : TipoAlineacionEnum::CAMPOS;
                $locationSiguiente = $locationSiguiente =  $toolController->DameSiguienteAlineacion($alineacion, $iddes, $id,TipoOrigenDatosEnum::BASEDATOS);       
                return $this->redirect($locationSiguiente);
            }
        } else {
            $descripcionDatos = $descripcionDatosManager->find($iddes, $request->getSession());
              //si es hijo quito el enlace a anterior
            if ($descripcionDatos->getDistribucion()>0) {
                $locationAnterior ="";
            }
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
                'muestraSiguiente' => $muestraSiguiente, 
                'archivoActual' => "",
                'ClassBody' => $this->ClassBody,
                'urlCrear' =>  $this->urlCrear,
                'urlAyuda' =>  $this->urlAyuda,
                'urlSoporte' =>  $this->urlSoporte,
                'urlMenu' =>  $this->urlMenu,
                'camposDistintos' => $camposDistintos,
                'camposAlineados' => $camposAlineados,
                'camposActuales' => $camposActuales,
                'permisoEdicion' => $permisoEdicion,
                'existe' => $existe,
                'origen_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }
  }
