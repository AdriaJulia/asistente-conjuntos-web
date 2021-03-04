<?php

namespace App\Controller;

use App\Enum\EstadoDescripcionDatosEnum;
use App\Service\Manager\OrigenDatosManager;
use App\Service\Manager\DescripcionDatosManager;
use App\Service\Processor\OrigenDatosFileFormProcessor;
use App\Service\Processor\OrigenDatosUrlFormProcessor;
use App\Service\Processor\OrigenDatosDataBaseFormProcessor;
use App\Enum\RutasAyudaEnum;
use App\Service\CurrentUser;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Psr\Log\LoggerInterface;


class OrigenDatosController extends AbstractController
{

    private $ClassBody = "asistente comunidad usuarioConectado";
    private $urlAyuda = "";
    private $urlCrear = "";

     /**
     * @Route("/asistentecamposdatos/{iddes}/url/origen",  requirements={"iddes"="\d+"}, name="insert_asistentecamposdatos_url")
     */
    public function InsertActionUrl(int $iddes,
                                    OrigenDatosUrlFormProcessor $origenDatosFormProcessor,
                                    OrigenDatosManager $origenDatosManager,
                                    DescripcionDatosManager $descripcionDatosManager,
                                    LoggerInterface $logger,
                                    Request $request) {
        $locationSiguiente = "";
        $errorProceso = "";
        $origenDatos = $origenDatosManager->new();    
        $id=null;
        $Istest = true;
        $locationAnterior = $this->generateUrl('update_asistentecamposdatos_paso3',["id"=>$iddes]);
        $this->urlCrear =  $this->generateUrl("insert_asistentecamposdatos_paso1");
        $this->urlAyuda = $this->generateUrl('asistentecamposdatos_ayuda_index',["pagina"=>RutasAyudaEnum::ORIGEN_DATOS_URL]);
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->urlAyuda .= "?locationAnterior={$actual_link}";
 
        [$form,$campos,$id,$Istest,$errorProceso]  = ($origenDatosFormProcessor)($iddes, $origenDatos, $request);
        if ($form->isSubmitted() && $form->isValid() && empty($errorProceso)) {
            if ($Istest) {
                $listaCampos = array();
                if (!empty($campos)) {
                    $listaCampos = explode(";",$campos);
                // $locationSiguiente = $this->generateUrl('insert_asistentecamposdatos_url',["iddes"=>$iddes]);
                }
                if (!empty($errorProceso)) {
                    $errorProceso = str_replace("error_proceso","Error del proceso", $errorProceso);
                }
                return $this->render('descripcion/origen.html.twig', [
                    'errorProceso' => $errorProceso,
                    'locationAnterior' => $locationAnterior,
                    'locationSigiente' => $locationSiguiente,
                    'campos' => $listaCampos,
                    'archivoActual' => "",
                    'ClassBody' => $this->ClassBody,
                    'urlCrear' =>  $this->urlCrear,
                    'urlAyuda' =>  $this->urlAyuda,
                    'permisoEdicion' => "block",
                    'origen_form' => $form->createView(),
                    'errors' => $form->getErrors()
                ]);
            } else {
                    return $this->redirectToRoute('insert_alineacion',["id"=>$id,"iddes"=>$iddes,"origen"=>"url"]); 
            }
        } else {
            $descripcionDatos = $descripcionDatosManager->find($iddes, $request->getSession());
            $permisoEdicion = ($descripcionDatos->getEstado() == EstadoDescripcionDatosEnum::BORRADOR  ||
                               $descripcionDatos->getEstado() == EstadoDescripcionDatosEnum::EN_CORRECCION) ? "block" : "none"; 
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
                'permisoEdicion' => $permisoEdicion,
                'origen_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }

     /**
     * @Route("/asistentecamposdatos/{iddes}/file/origen",  requirements={"iddes"="\d+"}, name="insert_asistentecamposdatos_file")
     */
    public function InsertActionFile(int $iddes,
                                     OrigenDatosFileFormProcessor $origenDatosFormProcessor,
                                     OrigenDatosManager $origenDatosManager,
                                     DescripcionDatosManager $descripcionDatosManager,
                                     LoggerInterface $logger,
                                     Request $request) {
        $locationSiguiente = "";
        $errorProceso = "";
        $archivoActual = "";
        $origenDatos = $origenDatosManager->new();
        $locationAnterior = $this->generateUrl('update_asistentecamposdatos_paso3',["id"=>$iddes]);
        $id=null;
        $Istest = true;
        $this->urlCrear =  $this->generateUrl("insert_asistentecamposdatos_paso1");
        $this->urlAyuda = $this->generateUrl('asistentecamposdatos_ayuda_index',["pagina"=>RutasAyudaEnum::ORIGEN_DATOS_FILE]);
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->urlAyuda .= "?locationAnterior={$actual_link}";
        [$form,$campos,$id, $Istest,  $archivoActual, $errorProceso] = ($origenDatosFormProcessor)($iddes,$origenDatos, $request);
        if ($form->isSubmitted() && $form->isValid() && empty($errorProceso)) {
        //     $this->addFlash('success', 'It sent!');
        if ($Istest) {
            $listaCampos = array();
                if (!empty($campos)) {
                    $listaCampos = explode(";",$campos);
                // $locationSiguiente = $this->generateUrl('insert_asistentecamposdatos_url',["iddes"=>$iddes]);
                }
                if (!empty($errorProceso)) {
                    $errorProceso = str_replace("error_proceso","Error del proceso", $errorProceso);
                }
                return $this->render('descripcion/origen.html.twig', [
                    'errorProceso' => $errorProceso,
                    'locationAnterior' => $locationAnterior,
                    'locationSigiente' => $locationSiguiente,
                    'campos' => $listaCampos,
                    'archivoActual' =>  $archivoActual,
                    'ClassBody' => $this->ClassBody,
                    'urlCrear' =>  $this->urlCrear,
                    'urlAyuda' =>  $this->urlAyuda,
                    'permisoEdicion' => "block",
                    'origen_form' => $form->createView(),
                    'errors' => $form->getErrors()
                ]);
            } else {
                return $this->redirectToRoute('insert_alineacion',["id"=>$id,"iddes"=>$iddes,"origen"=>"file"]); 
            }
        } else {
            $descripcionDatos = $descripcionDatosManager->find($iddes, $request->getSession());
            $permisoEdicion = ($descripcionDatos->getEstado() == EstadoDescripcionDatosEnum::BORRADOR  ||
                               $descripcionDatos->getEstado() == EstadoDescripcionDatosEnum::EN_CORRECCION) ? "block" : "none"; 

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
                'permisoEdicion' => $permisoEdicion,
                'origen_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }


    /**
     * @Route("/asistentecamposdatos/{iddes}/database/origen",  requirements={"iddes"="\d+"}, name="insert_asistentecamposdatos_database")
     */
    public function InsertActionDataBase(int $iddes,
                                         OrigenDatosDataBaseFormProcessor $origenDatosFormProcessor,
                                         OrigenDatosManager $origenDatosManager,
                                         DescripcionDatosManager $descripcionDatosManager,
                                         LoggerInterface $logger,
                                         Request $request) {
        $locationSiguiente = "";
        $errorProceso = "";
        $origenDatos = $origenDatosManager->new();
        $locationAnterior = $this->generateUrl('update_asistentecamposdatos_paso3',["id"=>$iddes]);
        $id=null;
        $Istest = true;
        $this->urlCrear =  $this->generateUrl("insert_asistentecamposdatos_paso1");
        $this->urlAyuda = $this->generateUrl('asistentecamposdatos_ayuda_index',["pagina"=>RutasAyudaEnum::ORIGEN_DATOS_DB]);
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->urlAyuda .= "?locationAnterior={$actual_link}";
        [$form,$campos,$id, $Istest, $errorProceso] = ($origenDatosFormProcessor)($iddes, $origenDatos, $request);
        if ($form->isSubmitted() && $form->isValid() && empty($errorProceso)) {
        //     $this->addFlash('success', 'It sent!');
        if ($Istest) {
                $listaCampos = array();
                if (!empty($campos)) {
                    $listaCampos = explode(";",$campos);
                // $locationSiguiente = $this->generateUrl('insert_asistentecamposdatos_url',["iddes"=>$iddes]);
                }
                if (!empty($errorProceso)) {
                    $errorProceso = str_replace("error_proceso","Error del proceso", $errorProceso);
                }
                return $this->render('descripcion/origen.html.twig', [
                    'errorProceso' => $errorProceso,
                    'locationAnterior' => $locationAnterior,
                    'locationSigiente' => $locationSiguiente,
                    'campos' => $listaCampos,
                    'archivoActual' => "",
                    'ClassBody' => $this->ClassBody,
                    'urlCrear' =>  $this->urlCrear,
                    'urlAyuda' =>  $this->urlAyuda,
                    'permisoEdicion' => "block",
                    'origen_form' => $form->createView(),
                    'errors' => $form->getErrors()
                ]);
            } else {
                return $this->redirectToRoute('insert_alineacion',["id"=>$id,"iddes"=>$iddes,"origen"=>"database"]); 
            }
        } else {
            $descripcionDatos = $descripcionDatosManager->find($iddes, $request->getSession());
            $permisoEdicion = ($descripcionDatos->getEstado() == EstadoDescripcionDatosEnum::BORRADOR  ||
                               $descripcionDatos->getEstado() == EstadoDescripcionDatosEnum::EN_CORRECCION) ? "block" : "none";
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
                'permisoEdicion' => $permisoEdicion,
                'origen_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }

     /**
     * @Route("/asistentecamposdatos/{iddes}/url/origen/{id}", requirements={"id"="\d+", "iddes"="\d+"}, name="update_asistentecamposdatos_url")
     */
    public function UpdateActionUrl(int $id,
                                    int $iddes,
                                    OrigenDatosUrlFormProcessor $origenDatosFormProcessor,
                                    OrigenDatosManager $origenDatosManager,
                                    DescripcionDatosManager $descripcionDatosManager,
                                    LoggerInterface $logger,
                                    Request $request) {

        $locationSiguiente = "";
        $errorProceso = "";
        $origenDatos = $origenDatosManager->find($id, $request->getSession());
        $locationAnterior = $this->generateUrl('update_asistentecamposdatos_paso3',["id"=>$iddes]);
        $id=null;
        $Istest = true;
        $this->urlCrear =  $this->generateUrl("insert_asistentecamposdatos_paso1");
        $this->urlAyuda = $this->generateUrl('asistentecamposdatos_ayuda_index',["pagina"=>RutasAyudaEnum::ORIGEN_DATOS_URL]);
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->urlAyuda .= "?locationAnterior={$actual_link}";
        [$form,$campos,$id, $Istest, $errorProceso] = ($origenDatosFormProcessor)($iddes, $origenDatos, $request);
        if ($form->isSubmitted() && $form->isValid() && empty($errorProceso)) {
        //     $this->addFlash('success', 'It sent!');
        if ($Istest) {
                $listaCampos = array();
                if (!empty($campos)) {
                    $listaCampos = explode(";",$campos);
                // $locationSiguiente = $this->generateUrl('insert_asistentecamposdatos_url',["iddes"=>$iddes]);
                }
                if (!empty($errorProceso)) {
                    $errorProceso = str_replace("error_proceso","Error del proceso", $errorProceso);
                }
                return $this->render('descripcion/origen.html.twig', [
                    'errorProceso' => $errorProceso,
                    'locationAnterior' => $locationAnterior,
                    'locationSigiente' => $locationSiguiente,
                    'campos' => $listaCampos,
                    'archivoActual' => "",
                    'ClassBody' => $this->ClassBody,
                    'urlCrear' =>  $this->urlCrear,
                    'urlAyuda' =>  $this->urlAyuda,
                    'permisoEdicion' => "block",
                    'origen_form' => $form->createView(),
                    'errors' => $form->getErrors()
                ]);
            } else {
                return $this->redirectToRoute('insert_alineacion',["id"=>$id,"iddes"=>$iddes,"origen"=>"url"]); 
            }
        } else {
            $descripcionDatos = $descripcionDatosManager->find($iddes, $request->getSession());
            $permisoEdicion = ($descripcionDatos->getEstado() == EstadoDescripcionDatosEnum::BORRADOR  ||
                               $descripcionDatos->getEstado() == EstadoDescripcionDatosEnum::EN_CORRECCION) ? "block" : "none";
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
                'permisoEdicion' => $permisoEdicion,
                'origen_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }

         /**
     * @Route("/asistentecamposdatos/{iddes}/file/origen/{id}", requirements={"id"="\d+", "iddes"="\d+"}, name="update_asistentecamposdatos_file")
     */
    public function UpdateActionFile(int $id,
                                     int $iddes,
                                     OrigenDatosFileFormProcessor $origenDatosFormProcessor,
                                     OrigenDatosManager $origenDatosManager,
                                     DescripcionDatosManager $descripcionDatosManager,
                                     LoggerInterface $logger,
                                     Request $request) {

        $locationSiguiente = "";
        $errorProceso = "";
        $archivoActual = "";
        $origenDatos = $origenDatosManager->find($id, $request->getSession());
        $locationAnterior = $this->generateUrl('update_asistentecamposdatos_paso3',["id"=>$iddes]);
        $id=null;
        $Istest = true;
        $this->urlCrear =  $this->generateUrl("insert_asistentecamposdatos_paso1");
        $this->urlAyuda = $this->generateUrl('asistentecamposdatos_ayuda_index',["pagina"=>RutasAyudaEnum::ORIGEN_DATOS_FILE]);
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->urlAyuda .= "?locationAnterior={$actual_link}";
        [$form,$campos,$id, $Istest,  $archivoActual, $errorProceso] = ($origenDatosFormProcessor)($iddes, $origenDatos, $request);
        if ($form->isSubmitted() && $form->isValid() && empty($errorProceso)) {
        //     $this->addFlash('success', 'It sent!');
        if ($Istest) {
                $listaCampos = array();
                if (!empty($campos)) {
                    $listaCampos = explode(";",$campos);
                // $locationSiguiente = $this->generateUrl('insert_asistentecamposdatos_url',["iddes"=>$iddes]);
                }
                if (!empty($errorProceso)) {
                    $errorProceso = str_replace("error_proceso","Error del proceso", $errorProceso);
                }
                return $this->render('descripcion/origen.html.twig', [
                    'errorProceso' => $errorProceso,
                    'locationAnterior' => $locationAnterior,
                    'locationSigiente' => $locationSiguiente,
                    'archivoActual' => $archivoActual,
                    'campos' => $listaCampos,
                    'ClassBody' => $this->ClassBody,
                    'urlCrear' =>  $this->urlCrear,
                    'urlAyuda' =>  $this->urlAyuda,
                    'permisoEdicion' => "block",
                    'origen_form' => $form->createView(),
                    'errors' => $form->getErrors()
                ]);
            } else {
                return $this->redirectToRoute('insert_alineacion',["id"=>$id,"iddes"=>$iddes,"origen"=>"file"]); 
            }
        } else {
            $descripcionDatos = $descripcionDatosManager->find($iddes, $request->getSession());
            $permisoEdicion = ($descripcionDatos->getEstado() == EstadoDescripcionDatosEnum::BORRADOR  ||
                               $descripcionDatos->getEstado() == EstadoDescripcionDatosEnum::EN_CORRECCION) ? "block" : "none";
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
                'permisoEdicion' => $permisoEdicion,
                'origen_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }

    /**
     * @Route("/asistentecamposdatos/{iddes}/database/origen/{id}", requirements={"id"="\d+", "iddes"="\d+"}, name="update_asistentecamposdatos_database")
     */
    public function UpdateActionDataBase(int $id,
                                         int $iddes,
                                         OrigenDatosDataBaseFormProcessor $origenDatosFormProcessor,
                                         OrigenDatosManager $origenDatosManager,
                                         DescripcionDatosManager $descripcionDatosManager,
                                         LoggerInterface $logger,
                                         Request $request) {

        $locationSiguiente = "";
        $errorProceso = "";
        $origenDatos = $origenDatosManager->find($id, $request->getSession());
        $locationAnterior = $this->generateUrl('update_asistentecamposdatos_paso3',["id"=>$iddes]);
        $id=null;
        $Istest = true;
        $this->urlCrear =  $this->generateUrl("insert_asistentecamposdatos_paso1");
        $this->urlAyuda = $this->generateUrl('asistentecamposdatos_ayuda_index',["pagina"=>RutasAyudaEnum::ORIGEN_DATOS_DB]);
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->urlAyuda .= "?locationAnterior={$actual_link}";
        [$form,$campos,$id, $Istest, $errorProceso] = ($origenDatosFormProcessor)($iddes, $origenDatos, $request);
        if ($form->isSubmitted() && $form->isValid() && empty($errorProceso)) {
        //     $this->addFlash('success', 'It sent!');
        if ($Istest) {
                $listaCampos = array();
                if (!empty($campos)) {
                    $listaCampos = explode(";",$campos);
                // $locationSiguiente = $this->generateUrl('insert_asistentecamposdatos_url',["iddes"=>$iddes]);
                }
                if (!empty($errorProceso)) {
                    $errorProceso = str_replace("error_proceso","Error del proceso", $errorProceso);
                }
                return $this->render('descripcion/origen.html.twig', [
                    'errorProceso' => $errorProceso,
                    'locationAnterior' => $locationAnterior,
                    'locationSigiente' => $locationSiguiente,
                    'campos' => $listaCampos,
                    'archivoActual' => "",
                    'ClassBody' => $this->ClassBody,
                    'urlCrear' =>  $this->urlCrear,
                    'urlAyuda' =>  $this->urlAyuda,
                    'permisoEdicion' => "block",
                    'origen_form' => $form->createView(),
                    'errors' => $form->getErrors()
                ]);
            } else {
                return $this->redirectToRoute('insert_alineacion',["id"=>$id,"iddes"=>$iddes,"origen"=>"database"]); 
            }
        } else {
            $descripcionDatos = $descripcionDatosManager->find($iddes, $request->getSession());
            $permisoEdicion = ($descripcionDatos->getEstado() == EstadoDescripcionDatosEnum::BORRADOR  ||
                               $descripcionDatos->getEstado() == EstadoDescripcionDatosEnum::EN_CORRECCION) ? "block" : "none";
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
                'permisoEdicion' => $permisoEdicion,
                'origen_form' => $form->createView(),
                'errors' => $form->getErrors()
            ]);
        }
    }

  }
