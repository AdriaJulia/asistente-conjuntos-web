<?php

namespace App\Service\Processor;

use App\Form\Type\SoporteFormType;
use App\Form\Model\SoporteDto;

use App\Service\Manager\SoporteManager;

use Symfony\Component\Form\FormFactoryInterface;
use App\Service\CurrentUser;
use Symfony\Component\HttpFoundation\Request;

/*
 * Descripción: Clase que realiza el trabajo de validar y enviar los datos al repositorio corespondiente
 *              Controla la validacion del formulario y serializa el Dto a la clase entidad
 *              Envía los datos a su persistencia a traves de repositorio  
 *              La clase se crea para el formulario de soporte ayuda y unicamente envía los datos para el envío del email
*/
class SoporteFormProcessor
{
    private $soporteManager;
    private $formFactory;

    public function __construct(
        SoporteManager $soporteManager,
        FormFactoryInterface $formFactory
    ) {
        $this->soporteManager = $soporteManager;
        $this->formFactory = $formFactory;
    }

    public function __invoke(Request $request): array
    { 
        $errorProceso= "";
        $soporte = new SoporteDto();
        $form = $this->formFactory->create(SoporteFormType::class, $soporte);  
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //envía los datos directamente del dto
            [$soporte,$errorProceso] = $this->soporteManager->envia($soporte,$request->getSession());                  
        }
        return [$form, $soporte];
    }  
}