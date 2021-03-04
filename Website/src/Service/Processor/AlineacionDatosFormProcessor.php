<?php

namespace App\Service\Processor;
use App\Enum\ModoFormularioAlineacionEnum;
use App\Enum\EstadoDescripcionDatosEnum;
use App\Service\RestApiRemote\RestApiClient;
use App\Form\Type\AlineacionDatosFormType;
use App\Form\Model\AlineacionDatosDto;
use App\Entity\OrigenDatos;
use App\Service\Manager\AlineacionDatosManager;
use App\Service\Manager\DescripcionDatosManager;
use Symfony\Component\Form\FormFactoryInterface;
use App\Service\CurrentUser;
use Symfony\Component\HttpFoundation\Request;


class AlineacionDatosFormProcessor
{
    private $currentUser;
    private $alineacionDatosManager;
    private $descripcionDatosManager;
    private $formFactory;
    private $clientHttprest;


    public function __construct(
        CurrentUser $currentUser,
        AlineacionDatosManager $alineacionDatosManager,
        DescripcionDatosManager $descripcionDatosManager,
        FormFactoryInterface $formFactory,
        RestApiClient $clientHttprest
    ) {
        $this->currentUser = $currentUser;
        $this->alineacionDatosManager = $alineacionDatosManager;
        $this->descripcionDatosManager = $descripcionDatosManager;
        $this->formFactory = $formFactory;
        $this->clientHttprest = $clientHttprest;
    }

    public function __invoke(int $idDescripcion,
                             OrigenDatos $origenDatos,
                             Request $request): array
    { 
        $id = "";
        $errorProceso= "";
        $campos = "";
        $prueba = false;
        $campos = explode(";",$origenDatos->getCampos());
        $alineacionDatosDto = AlineacionDatosDto::createFromAlineacionDatos($origenDatos);
        if (!empty($origenDatos->getId())){
            if (empty($_POST['alineacionEntidad'])){
                $options  = array('allowed_campos' => $campos, 'allowed_ontologias'=>array());
            } else {
                $ontologias = $this->clientHttprest->GetOntologia($_POST['alineacionEntidad']);
                $options  = array('allowed_campos' => $campos, 'allowed_ontologias'=>$ontologias);
            }
            $form = $this->formFactory->create(AlineacionDatosFormType::class, $alineacionDatosDto, $options);
        }
        $form->handleRequest($request);
        $modoFormulario = $alineacionDatosDto->modoFormulario;
        if ($form->isSubmitted()) {
            $alineacionDatosDto = $form->getData(); 
            $guardar = ($modoFormulario== ModoFormularioAlineacionEnum::Guardar);
            $omitir = ($modoFormulario== ModoFormularioAlineacionEnum::Omitir);
            $seleccion = ($modoFormulario==ModoFormularioAlineacionEnum::Seleccion);
            
            if ($form->isValid()) { 
                if ($guardar) {
                    $origenDatos->setAlineacionEntidad($alineacionDatosDto->alineacionEntidad);
                    $origenDatos->setAlineacionRelaciones(base64_encode($alineacionDatosDto->alineacionRelaciones));
                    $origenDatos->setSesion($request->getSession()->getId());
                    $origenDatos->updatedTimestamps();
                    [$origenDatos,$errorProceso] = $this->alineacionDatosManager->saveAlineacionDatosEntidad($origenDatos,$request->getSession());                  
                }    
                if ($omitir || $guardar) {
                    $descripcionDatos = $this->descripcionDatosManager->find($idDescripcion,$request->getSession());
                    $descripcionDatos->setSesion($request->getSession()->getId());
                    $descripcionDatos->setEstado(EstadoDescripcionDatosEnum::BORRADOR);
                    $descripcionDatos->setDescripcion("");
                    $this->descripcionDatosManager->saveWorkflow($descripcionDatos,$request->getSession()); 
                } 
            } 
        }
        return [$form, $modoFormulario, $origenDatos];
    }  
}