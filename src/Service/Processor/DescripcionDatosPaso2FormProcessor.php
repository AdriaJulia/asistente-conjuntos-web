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
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
/*
 * Descripción: Clase que realiza el trabajo de validar y enviar los datos al repositorio corespondiente
 *              Controla la validación del formulario y serializa el Dto a la clase entidad
 *              Envía los datos a su persistencia a través de repositorio  
 *              La clase se crea para el formulario de descripción de datos Paso 2
*/
class DescripcionDatosPaso2FormProcessor
{
    private $currentUser;
    private $descripcionDatosManager;
    private $formFactory;

    public function __construct(
        CurrentUser $currentUser,
        DescripcionDatosManager $descripcionDatosManager,
        ContainerBagInterface $params,
        FormFactoryInterface $formFactory
    ) {
        $this->currentUser = $currentUser;
        $this->descripcionDatosManager = $descripcionDatosManager;
        $this->formFactory = $formFactory;
        $this->params = $params;
    }

    public function __invoke(DescripcionDatos $descripcionDatos,
                             Request $request): array
    { 

        if (empty($request->getSession()->getId())) {
            session_start(); 
        }

        //inicializo con el la descripcion de los datos
        $descripcionDatosDto = DescripcionDatosDto::createFromDescripcionDatos($descripcionDatos);
        $proximoEstadoAlta = $descripcionDatos->getEstadoAlta();
        //asigno el estado del asistente
        if ($proximoEstadoAlta == EstadoAltaDatosEnum::PASO2) {
            $proximoEstadoAlta = EstadoAltaDatosEnum::ORIGEN_URL;
        }
        $form = $this->formFactory->create(DescripcionDatosPaso2FormType::class, $descripcionDatosDto);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //recojo los datos del formulario
            $descripcionDatos->setPublicador($descripcionDatosDto->publicador);
           
            $descripcionDatos->setVocabularios($descripcionDatosDto->diccionarioDatos['vocabularios']);
            $descripcionDatos->setDescripcionVocabularios($descripcionDatosDto->diccionarioDatos['descripcion']);

            $descripcionDatos->setServicios($descripcionDatosDto->calidadDato['servicios']);
            $descripcionDatos->setDescripcionServicios($descripcionDatosDto->calidadDato['descripcion']);

            $descripcionDatos->setLicencias($this->params->get("licencia_conjunto_datos"));

            $descripcionDatos->setSesion($request->getSession()->getId());
            $descripcionDatos->updatedTimestamps();
            $descripcionDatos->setEstadoAlta($proximoEstadoAlta);
            //guardo
            $descripcionDatos = $this->descripcionDatosManager->save($descripcionDatos,$request->getSession()); 
            
        }
        return [$form];
    }
}


