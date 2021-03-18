<?php

namespace App\Service\Processor;

use App\Form\Type\DescripcionDatosWorkFlowFormType;
use App\Form\Model\DescripcionDatosDto;
use App\Entity\DescripcionDatos;
use App\Service\Manager\DescripcionDatosManager;

use Symfony\Component\Form\FormFactoryInterface;
use App\Service\CurrentUser;
use Symfony\Component\HttpFoundation\Request;


class DescripcionDatosWorkFlowFormProcessor
{
    private $currentUser;
    private $descripcionDatosManager;
    private $formFactory;

    public function __construct(
        CurrentUser $currentUser,
        DescripcionDatosManager $descripcionDatosManager,
        FormFactoryInterface $formFactory
    ) {
        $this->currentUser = $currentUser;
        $this->descripcionDatosManager = $descripcionDatosManager;
        $this->formFactory = $formFactory;
    }

    public function __invoke(DescripcionDatos $descripcionDatos,
                             Request $request): array
    { 

        $descripcionDatosDto = DescripcionDatosDto::createFromDescripcionDatos($descripcionDatos);
        $form = $this->formFactory->create(DescripcionDatosWorkFlowFormType::class, $descripcionDatosDto);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $descripcionDatos->setSesion($request->getSession()->getId());
            $descripcionDatos->setDescripcion($descripcionDatosDto->descripcion);
            $descripcionDatos->setEstado($descripcionDatosDto->estado);
            $descripcionDatos->updatedTimestamps();
            
            $descripcionDatos = $this->descripcionDatosManager->saveWorkflow($descripcionDatos,$request->getSession()); 
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
            
        }
        return [$form];
    }
}
