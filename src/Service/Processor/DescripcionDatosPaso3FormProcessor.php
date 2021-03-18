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

/*
 * Descripción: Clase que realiza el trabajo de validar y enviar los datos al repositorio corespondiente
 *              Controla la validacion del formulario y serializa el Dto a la clase entidad
 *              Envía los datos a su persistencia a traves de repositorio  
 *              La clase se crea para el formulario de descripcion de datos Paso1.3
*/
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

        //inicializo con el la descripcion de los datos
        $descripcionDatosDto = DescripcionDatosDto::createFromDescripcionDatos($descripcionDatos);
        $proximoEstadoAlta = $descripcionDatos->getEstadoAlta();
        //asigno el estado del asistente
        if ($proximoEstadoAlta==EstadoAltaDatosEnum::paso3) {
            $proximoEstadoAlta=EstadoAltaDatosEnum::origen_url;
        }
        $form = $this->formFactory->create(DescripcionDatosPaso3FormType::class, $descripcionDatosDto);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //recojo los datos del formulario
            $descripcionDatos->setEstructura($descripcionDatosDto->estructura);
            $descripcionDatos->setEstructuraDenominacion($descripcionDatosDto->estructuraDenominacion);
            $descripcionDatos->setLicencias($descripcionDatosDto->licencias);
            $descripcionDatos->setFormatos($descripcionDatosDto->formatos);
            $descripcionDatos->setEtiquetas($descripcionDatosDto->etiquetas);
            
            $descripcionDatos->setSesion($request->getSession()->getId());
            $descripcionDatos->setEstadoAlta($proximoEstadoAlta);

            $descripcionDatos->updatedTimestamps();
            //guardo
            $descripcionDatos = $this->descripcionDatosManager->save($descripcionDatos,$request->getSession()); 
            
        }
        return [$form];
    }
}