<?php

namespace App\Service\Processor;

use App\Enum\EstadoAltaDatosEnum;
use App\enum\FrecuenciaActualizacionEnum;
use App\Entity\DescripcionDatos;

use App\Form\Type\DescripcionDatosPaso1FormType;
use App\Form\Model\DescripcionDatosDto;

use App\Service\Manager\DescripcionDatosManager;

use Ramsey\Uuid\Uuid;
use Symfony\Component\Form\FormFactoryInterface;
use App\Service\CurrentUser;
use App\Enum\EstadoDescripcionDatosEnum;

use Symfony\Component\HttpFoundation\Request;


class DescripcionDatosPaso1FormProcessor
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
        if (!empty($descripcionDatos->getId())){
            $descripcionDatosDto = DescripcionDatosDto::createFromDescripcionDatos($descripcionDatos);
            $form = $this->formFactory->create(DescripcionDatosPaso1FormType::class, $descripcionDatosDto);
            $proximoEstadoAlta = $descripcionDatos->getEstadoAlta();
            if ($proximoEstadoAlta!=EstadoAltaDatosEnum::paso2) {
                $proximoEstadoAlta=EstadoAltaDatosEnum::paso2;
            }  
        } else {
            $form = $this->formFactory->create(DescripcionDatosPaso1FormType::class); 
            $proximoEstadoAlta = EstadoAltaDatosEnum::paso2;
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
   
            $uuidGenerator = Uuid::uuid4();
            $descripcionDatosDto = $form->getData();   

            $descripcionDatos->setDenominacion($descripcionDatosDto->denominacion);
            $descripcionDatos->setIdentificacion($uuidGenerator->toString());
            $descripcionDatos->setDescripcion($descripcionDatosDto->descripcion);
            $descripcionDatos->setTerritorio($descripcionDatosDto->territorio);
            
            $descripcionDatos->setFrecuenciaActulizacion($descripcionDatosDto->frecuenciaActulizacion);    
            $descripcionDatos->setFechaInicio($descripcionDatosDto->fechaInicio);
            $descripcionDatos->setFechaFin($descripcionDatosDto->fechaFin);
            $descripcionDatos->setInstancias($descripcionDatosDto->instancias);
        
            $username = $this->currentUser->getCurrentUser()->getUsername();
            $descripcionDatos->setUsuario($username);
            $descripcionDatos->setSesion($request->getSession()->getId());
            $descripcionDatos->setEstado(EstadoDescripcionDatosEnum::BORRADOR);
            $descripcionDatos->setEstadoAlta($proximoEstadoAlta);

            $descripcionDatos->updatedTimestamps();
            if (empty($descripcionDatosDto->id)){
                $descripcionDatos = $this->descripcionDatosManager->create($descripcionDatos,$request->getSession());  
            } else {
                $descripcionDatos = $this->descripcionDatosManager->save($descripcionDatos,$request->getSession()); 
            }
        }
        return [$form, $descripcionDatos];
    }
}