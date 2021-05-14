<?php

namespace App\Service\Processor;

use App\Service\RestApiRemote\RestApiClient;
use App\Enum\EstadoDescripcionDatosEnum;
use App\Enum\TipoOrigenDatosEnum;
use App\Form\Type\DescripcionDatosWorkFlowFormType;
use App\Form\Model\DescripcionDatosDto;
use App\Entity\DescripcionDatos;
use App\Service\Manager\DescripcionDatosManager;

use Symfony\Component\Form\FormFactoryInterface;
use App\Service\CurrentUser;
use Symfony\Component\HttpFoundation\Request;

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

    public function __construct(
        CurrentUser $currentUser,
        DescripcionDatosManager $descripcionDatosManager,
        RestApiClient $restApiClient,
        FormFactoryInterface $formFactory
    ) {
        $this->currentUser = $currentUser;
        $this->descripcionDatosManager = $descripcionDatosManager;
        $this->formFactory = $formFactory;
        $this->restApiClient = $restApiClient;
    }

    public function __invoke(DescripcionDatos $descripcionDatos,
                             Request $request): array
    { 

         //el origen de datos actual nunca  es nuevo
        $descripcionDatosDto = DescripcionDatosDto::createFromDescripcionDatos($descripcionDatos);
        //inicializo con el origen de datos
        $form = $this->formFactory->create(DescripcionDatosWorkFlowFormType::class, $descripcionDatosDto);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //recojo los datos del formulario
            $descripcionDatos->setSesion($request->getSession()->getId());
            $descripcionDatos->setDescripcion($descripcionDatosDto->descripcion);
            $descripcionDatos->setEstado($descripcionDatosDto->estado);
            $descripcionDatos->setProcesaAdo($descripcionDatosDto->porcesaAdo);
            $descripcionDatos->updatedTimestamps();
             
            $origendatos = $descripcionDatos->getOrigenDatos();
            if (($descripcionDatosDto->estado==EstadoDescripcionDatosEnum::VALIDADO) && 
                ($origendatos->getTipoOrigen() ==TipoOrigenDatosEnum::BASEDATOS))
            {
                $uriGaodecore = $origendatos->getGaodcoreUri();
                $IdGaodecore =  $this->restApiClient->GetGaodcoreResource($uriGaodecore,$origendatos->getEsquema(),true,$descripcionDatos->getIdentificacion());   
                $descripcionDatos->setGaodcoreResourceId($IdGaodecore['resourceid']);
            } else {
                $descripcionDatos->setGaodcoreResourceId("");
            }
            /*
            switch ($descripcionDatosDto->estado) {
                case EstadoDescripcionDatosEnum::EN_ESPERA:
                    $this->mailtool->sendEmail($descripcionDatos, $descripcionDatosDto->descripcion);
                    break;
                case EstadoDescripcionDatosEnum::DESECHADO:
                    $this->mailtool->sendEmail($descripcionDatos, $descripcionDatosDto->descripcion);
                    break;
                case EstadoDescripcionDatosEnum::VALIDADO:
                    $this->mailtool->sendEmail($descripcionDatos, $descripcionDatosDto->descripcion);
                    break;
                case EstadoDescripcionDatosEnum::EN_CORRECCION:
                    $this->mailtool->sendEmail($descripcionDatos, $descripcionDatosDto->descripcion);
                    break;
            }
*/
           //envío a apirest
            $descripcionDatos = $this->descripcionDatosManager->saveWorkflow($descripcionDatos,$request->getSession()); 
            
            
        }
        return [$form];
    }
}
