<?php

namespace App\Service\Processor;
use App\Enum\EstadoAltaDatosEnum;
use App\Form\Type\DescripcionDatosPaso2FormType;
use App\Form\Model\DescripcionDatosDto;
use App\Entity\DescripcionDatos;
use App\Service\Manager\DescripcionDatosManager;

use Symfony\Component\Form\FormFactoryInterface;
use App\Service\CurrentUser;
use Symfony\Component\HttpFoundation\Request;


class DescripcionDatosPaso2FormProcessor
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
        $proximoEstadoAlta = $descripcionDatos->getEstadoAlta();
        if ($proximoEstadoAlta == EstadoAltaDatosEnum::paso2) {
            $proximoEstadoAlta = EstadoAltaDatosEnum::paso3;
        }
        $form = $this->formFactory->create(DescripcionDatosPaso2FormType::class, $descripcionDatosDto);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $descripcionDatos->setOrganoResponsable($descripcionDatosDto->organoResponsable);
            $descripcionDatos->setFinalidad($descripcionDatosDto->finalidad);
            $descripcionDatos->setCondiciones($descripcionDatosDto->condiciones);
            $descripcionDatos->setLicencias($descripcionDatosDto->licencias);
            $descripcionDatos->setVocabularios($descripcionDatosDto->vocabularios);
            $descripcionDatos->setServicios($descripcionDatosDto->servicios);

            $descripcionDatos->setSesion($request->getSession()->getId());
            $descripcionDatos->updatedTimestamps();
            $descripcionDatos->setEstadoAlta($proximoEstadoAlta);

            $descripcionDatos = $this->descripcionDatosManager->save($descripcionDatos,$request->getSession()); 
            
        }
        return [$form];
    }
}


