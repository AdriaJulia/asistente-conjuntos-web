<?php

namespace App\Service\Processor;


use App\Enum\EstadoAltaDatosEnum;
use App\Enum\EstadoDistribucionEnum;
use App\enum\FrecuenciaActualizacionEnum;
use App\Entity\DescripcionDatos;
use App\Enum\EstadoDescripcionDatosEnum;

use App\Form\Type\DescripcionDatosPaso1FormType;
use App\Form\Model\DescripcionDatosDto;

use App\Service\Manager\DescripcionDatosManager;

use Symfony\Component\Form\FormFactoryInterface;
use App\Service\CurrentUser;

use App\Service\Processor\Tool\ProcessorTool; 

use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;

/*
 * Descripción: Clase que realiza el trabajo de validar y enviar los datos al repositorio corespondiente
 *              Controla la validacion del formulario y serializa el Dto a la clase entidad
 *              Envía los datos a su persistencia a traves de repositorio  
 *              La clase se crea para el formulario de descripcion de datos Paso1.1 
*/
class DescripcionDatosPaso1FormProcessor
{
    private $currentUser;
    private $descripcionDatosManager;
    private $formFactory;
    private $logger;

    public function __construct(
        CurrentUser $currentUser,
        DescripcionDatosManager $descripcionDatosManager,
        FormFactoryInterface $formFactory,
        LoggerInterface $logger
    ) {
        $this->currentUser = $currentUser;
        $this->descripcionDatosManager = $descripcionDatosManager;
        $this->formFactory = $formFactory;
        $this->logger = $logger;
    }

    public function __invoke(DescripcionDatos $descripcionDatos,
                             Request $request): array
    { 
        
        if (empty($request->getSession()->getId())) {
            session_start(); 
        }

         //si el origen de datos actual no es nuevo
        if (!empty($descripcionDatos->getId())){
            // creo el formulario vacío con los datos actuales
            $descripcionDatosDto = DescripcionDatosDto::createFromDescripcionDatos($descripcionDatos);
            $form = $this->formFactory->create(DescripcionDatosPaso1FormType::class, $descripcionDatosDto);
            $proximoEstadoAlta = $descripcionDatos->getEstadoAlta();
             //asigno el estado del asistente
            if ($proximoEstadoAlta!=EstadoAltaDatosEnum::PASO2) {
                $proximoEstadoAlta=EstadoAltaDatosEnum::PASO2;
            }  
        } else {
            //creo el formulario vacío 
            $form = $this->formFactory->create(DescripcionDatosPaso1FormType::class); 
            $proximoEstadoAlta = EstadoAltaDatosEnum::PASO2;
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //recojo los datos del formulario
            $descripcionDatosDto = $form->getData();   

            $descripcionDatos->setTitulo($descripcionDatosDto->titulo);
            $descripcionDatos->setIdentificacion(ProcessorTool::clean($descripcionDatosDto->titulo));
            $descripcionDatos->setDescripcion($descripcionDatosDto->descripcion);
            $descripcionDatos->setTematica($descripcionDatosDto->tematica);
            $descripcionDatos->setEtiquetas($descripcionDatosDto->etiquetas);
            $descripcionDatos->setFrecuenciaActulizacion($descripcionDatosDto->frecuenciaActulizacion);  
            $descripcionDatos->setFechaInicio($descripcionDatosDto->coberturaTemporal['fechaInicio']);
            $descripcionDatos->setFechaFin($descripcionDatosDto->coberturaTemporal['fechaFin']);       
            $descripcionDatos->setCoberturaGeografica($descripcionDatosDto->coberturaGeografica);
            $idiomas = $this->dameIdiomas($descripcionDatosDto->coberturaIdioma);
            $descripcionDatos->setIdiomas($idiomas);
            $descripcionDatos->setNivelDetalle($descripcionDatosDto->nivelDetalle);



            // esto es para poder hacer los test unitarios sin LDAP
            if ($this->currentUser->getCurrentUser()!=null){
                $username = $this->currentUser->getCurrentUser()->getExtraFields()['mail'];
            } else {
                $username = "MOCKSESSID";
            }
        
            $descripcionDatos->setUsuario($username);
            $descripcionDatos->setSesion($request->getSession()->getId());
            $descripcionDatos->setEstado(EstadoDescripcionDatosEnum::BORRADOR);
            $descripcionDatos->setEstadoAlta($proximoEstadoAlta);
          //ahora distingo si la llamada es de un origen nuevo o existente 
            $descripcionDatos->updatedTimestamps();
            if (empty($descripcionDatosDto->id)){
                //si es nuevo no tiene hijos
                $descripcionDatos->setDistribucion(EstadoDistribucionEnum::SIN_HIJOS);
                $descripcionDatos = $this->descripcionDatosManager->create($descripcionDatos,$request->getSession());  
            } else {
                $descripcionDatos = $this->descripcionDatosManager->save($descripcionDatos,$request->getSession()); 
            }
        }
        return [$form, $descripcionDatos];
    }

    private function dameIdiomas($coverturaIdiomas) : string{
        $idiomas = "";
        foreach ($coverturaIdiomas['lenguajes'] as $key=>$value) {
           if ($value!=="Otro") {
            $idiomas .= $value . ",";
           } else {
             $idiomas .= $coverturaIdiomas['otroslenguajes'] . ",";
           }
        }
        $idiomas = (strlen($idiomas)>0)? substr($idiomas,0,-1):$idiomas; 
        return $idiomas;
    }
}

 