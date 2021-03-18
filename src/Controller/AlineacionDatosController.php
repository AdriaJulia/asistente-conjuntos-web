<?php

namespace App\Controller;

use App\Enum\EstadoDescripcionDatosEnum;
use App\Service\Manager\OrigenDatosManager;
use App\Service\Manager\DescripcionDatosManager;
use App\Enum\RutasAyudaEnum;
use App\Enum\ModoFormularioAlineacionEnum;;
use App\Service\Processor\AlineacionDatosFormProcessor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Psr\Log\LoggerInterface;


class AlineacionDatosController extends AbstractController
{

    private $ClassBody = "asistente comunidad usuarioConectado";
    private $urlAyuda = "";
    private $urlCrear = "";


    /**
    * @Route("/asistentecamposdatos/{iddes}/{origen}/origen/{id}/alineacion", requirements={"iddes"="\d+", "id"="\d+", "origen"="url|file|database"}, name="insert_alineacion")
    */
   public function InsertAction(int $iddes,
                                int $id,
                                string $origen,
                                AlineacionDatosFormProcessor $alineacionDatosFormProcessor,
                                OrigenDatosManager $origenDatosManager,
                                DescripcionDatosManager $descripcionDatosManager,
                                LoggerInterface $logger,
                                Request $request) {

       $locationSiguiente = "";
       $locationAnterior = "";
       $errorProceso = "";
       $this->urlCrear =  $this->generateUrl("insert_asistentecamposdatos_paso1");
       $this->urlAyuda = $this->generateUrl('asistentecamposdatos_ayuda_index',["pagina"=>RutasAyudaEnum::ALINEACION_DATOS]);
       $host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
       $actual_link =  "$host$_SERVER[REQUEST_URI]";
       $this->urlAyuda .= "?locationAnterior={$actual_link}";
       switch ($origen) {
           case 'url':
            $locationAnterior = $this->generateUrl('update_asistentecamposdatos_url',["id"=>$id, "iddes"=>$iddes]);
               break;
           case 'file':
            $locationAnterior = $this->generateUrl('update_asistentecamposdatos_file',["id"=>$id, "iddes"=>$iddes]);
                break;  
           case 'database':
            $locationAnterior = $this->generateUrl('update_asistentecamposdatos_database',["id"=>$id, "iddes"=>$iddes]);
                break;          
       }
       $locationAnterior = "$host$locationAnterior";
       $origenDatos = $origenDatosManager->find($id, $request->getSession());
       [$form,$modoFormulario, $origenDatos] = ($alineacionDatosFormProcessor)($iddes, $origenDatos, $request);
       $descripcionDatos = $descripcionDatosManager->find($iddes, $request->getSession());
       $permisoEdicion = ($descripcionDatos->getEstado() == EstadoDescripcionDatosEnum::BORRADOR  ||
                          $descripcionDatos->getEstado() == EstadoDescripcionDatosEnum::EN_CORRECCION) ? "block" : "none"; 
       if ($form->isSubmitted() && $form->isValid()) {
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
            'permisoEdicion' => $permisoEdicion
           ]);        
       }
       return $this->render('alineacion/seleccion.html.twig', [
        'errorProceso' => $errorProceso,
        'locationAnterior' => $locationAnterior,
        'alineacion_form' => $form->createView(),
        'ClassBody' => $this->ClassBody,
        'urlCrear' =>  $this->urlCrear,
        'urlAyuda' =>  $this->urlAyuda,
        'permisoEdicion' => $permisoEdicion
    ]);
   }

}

