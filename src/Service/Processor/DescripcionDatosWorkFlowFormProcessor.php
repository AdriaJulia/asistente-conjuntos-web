<?php

namespace App\Service\Processor;

use App\Service\RestApiRemote\GaodCoreRestApiClient;
use App\Enum\EstadoDescripcionDatosEnum;
use App\Enum\TipoOrigenDatosEnum;
use App\Form\Type\DescripcionDatosWorkFlowFormType;
use App\Form\Model\DescripcionDatosDto;
use App\Entity\DescripcionDatos;
use App\Service\Manager\DescripcionDatosManager;

use Symfony\Component\Form\FormFactoryInterface;
use App\Service\CurrentUser;
use Symfony\Component\HttpFoundation\Request;

use Psr\Log\LoggerInterface;

/*
 * Descripción: Clase que realiza el trabajo de validar y enviar los datos al repositorio corespondiente
 *              Controla la validacion del formulario y serializa el Dto a la clase entidad
 *              Envía los datos a su persistencia a traves de repositorio  
 *              La clase se crea para el formulario cambio de estado (botones de la ficha)
*/
class DescripcionDatosWorkFlowFormProcessor
{
    private $currentUser;
    private $descripcionDatosManager;
    private $formFactory;
    private $restApiClient;
    private $logger;

    public function __construct(
        CurrentUser $currentUser,
        DescripcionDatosManager $descripcionDatosManager,
        GaodCoreRestApiClient $restApiClient,
        FormFactoryInterface $formFactory,
        LoggerInterface $logger
    ) {
        $this->currentUser = $currentUser;
        $this->descripcionDatosManager = $descripcionDatosManager;
        $this->formFactory = $formFactory;
        $this->restApiClient = $restApiClient;
        $this->logger = $logger;{}
    }

    public function __invoke(DescripcionDatos $descripcionDatos,
                             Request $request): array
    { 
        $errorProceso = "";
        $error = "";
        if (empty($request->getSession()->getId())) {
            session_start(); 
        }
         //el origen de datos actual nunca  es nuevo
        $descripcionDatosDto = DescripcionDatosDto::createFromDescripcionDatos($descripcionDatos);
        //inicializo con el origen de datos
        $form = $this->formFactory->create(DescripcionDatosWorkFlowFormType::class, $descripcionDatosDto);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //recojo los datos del formulario
                    //si no aparece el check hay que tratarlo ahora
            $descripcionDatosDto->porcesaAdo = ($descripcionDatosDto->porcesaAdo==null) ? "0" : $descripcionDatosDto->porcesaAdo;
            $descripcionDatos->setSesion($request->getSession()->getId());
            $descripcionDatos->setDescripcion($descripcionDatosDto->descripcion); 
            $descripcionDatos->setEstado($descripcionDatosDto->estado);
            $descripcionDatos->setProcesaAdo($descripcionDatosDto->porcesaAdo);
            $descripcionDatos->updatedTimestamps();
             
            $resourceid ="";
            $origendatos = $descripcionDatos->getOrigenDatos();

            if (($descripcionDatosDto->estado==EstadoDescripcionDatosEnum::VALIDADO) && 
                ($origendatos->getTipoOrigen() ==TipoOrigenDatosEnum::BASEDATOS) && empty($descripcionDatos->getGaodcoreResourceId()))
            {
                $object_location = $origendatos->getTabla();
                $object_location_schema = "";
                $temp = explode(".",$origendatos->getTabla());
                if (count($temp)==2) {
                    $object_location_schema = $temp[0];
                    $object_location = $temp[1];
                }

                $uriGaodecore = $origendatos->getGaodcoreUri();
                $nameresource = $origendatos->getId() . " " .  $origendatos->getNombre();
                [$IdGaodecore, $error] =  $this->restApiClient->GetGaodcoreResource($uriGaodecore, 
                                                                                    $object_location_schema, 
                                                                                    $object_location, 
                                                                                    true, 
                                                                                    $nameresource); 
                if (empty($error)) {
                    $resourceid = $IdGaodecore['resourceid'];
                }
            }
            $descripcionDatos->setGaodcoreResourceId($resourceid);
   
            if (!empty($error)){
                $errorProceso = $error;
                $this->logger->error($errorProceso);
            }

            [$descripcionDatos,$error] = $this->descripcionDatosManager->saveWorkflow($descripcionDatos,$request->getSession());  
            if (!empty($error)){
                $errorProceso .= " " . $error;
                $this->logger->error($errorProceso);
            } 
        }
        return [$form,$errorProceso];
    }
}
