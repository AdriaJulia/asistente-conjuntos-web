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

/*
 * Descripción: Clase que realiza el trabajo de validar y enviar los datos al repositorio corespondiente
 *              Controla la validacion del formulario y serializa el Dto a la clase entidad
 *              Envía los datos a su persistencia a traves de repositorio  
 *              La clase se crea para el formulario de descripcion de datos Paso1.2
*/
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

        //inicializo con el la descripcion de los datos
        $descripcionDatosDto = DescripcionDatosDto::createFromDescripcionDatos($descripcionDatos);
        $proximoEstadoAlta = $descripcionDatos->getEstadoAlta();
        //asigno el estado del asistente
        if ($proximoEstadoAlta == EstadoAltaDatosEnum::paso2) {
            $proximoEstadoAlta = EstadoAltaDatosEnum::paso3;
        }
        $form = $this->formFactory->create(DescripcionDatosPaso2FormType::class, $descripcionDatosDto);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
               //recojo los datos del formulario
            $descripcionDatos->setOrganoResponsable($descripcionDatosDto->organoResponsable);
            $descripcionDatos->setFinalidad($descripcionDatosDto->finalidad);
            $descripcionDatos->setCondiciones($descripcionDatosDto->condiciones);
            $descripcionDatos->setLicencias($descripcionDatosDto->licencias);
            $descripcionDatos->setVocabularios($descripcionDatosDto->vocabularios);
            $descripcionDatos->setServicios($descripcionDatosDto->servicios);

            $descripcionDatos->setSesion($request->getSession()->getId());
            $descripcionDatos->updatedTimestamps();
            $descripcionDatos->setEstadoAlta($proximoEstadoAlta);
            //guardo
            $descripcionDatos = $this->descripcionDatosManager->save($descripcionDatos,$request->getSession()); 
            
        }
        return [$form];
    }
}


