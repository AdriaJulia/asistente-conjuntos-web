<?php

namespace App\Service\Processor;

use App\Enum\EstadoAltaDatosEnum;

use App\Form\Type\DescripcionDatosPaso3FormType;
use App\Form\Model\DescripcionDatosDto;
use App\Entity\DescripcionDatos;
use App\Service\Manager\DescripcionDatosManager;

use Symfony\Component\Form\FormFactoryInterface;
use App\Service\CurrentUser;
use Symfony\Component\HttpFoundation\Request;


class DescripcionDatosPaso3FormProcessor
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
        if ($proximoEstadoAlta==EstadoAltaDatosEnum::paso3) {
            $proximoEstadoAlta=EstadoAltaDatosEnum::origen_url;
        }
        $form = $this->formFactory->create(DescripcionDatosPaso3FormType::class, $descripcionDatosDto);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $descripcionDatos->setEstructura($descripcionDatosDto->estructura);
            $descripcionDatos->setEstructuraDenominacion($descripcionDatosDto->estructuraDenominacion);
            $descripcionDatos->setAspectosFormales($descripcionDatosDto->aspectosFormales);
            $descripcionDatos->setLicencias($descripcionDatosDto->licencias);
            $descripcionDatos->setFormatos($descripcionDatosDto->formatos);
            $descripcionDatos->setEtiquetas($descripcionDatosDto->etiquetas);
            
            $descripcionDatos->setSesion($request->getSession()->getId());
            $descripcionDatos->setEstadoAlta($proximoEstadoAlta);

            $descripcionDatos->updatedTimestamps();
            
            $descripcionDatos = $this->descripcionDatosManager->save($descripcionDatos,$request->getSession()); 
            
        }
        return [$form];
    }
}