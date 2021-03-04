<?php

namespace App\Service\Processor;

use App\Enum\ModoFormularioOrigenEnum;
use App\Enum\TipoOrigenDatosEnum;;
use App\Form\Type\OrigenDatosUrlFormType;
use App\Form\Model\OrigenDatosDto;
use App\Entity\OrigenDatos;
use App\Service\Manager\OrigenDatosManager;
use Symfony\Component\Form\FormFactoryInterface;
use App\Service\CurrentUser;
use Symfony\Component\HttpFoundation\Request;


class OrigenDatosUrlFormProcessor
{
    private $currentUser;
    private $origenDatosManager;
    private $formFactory;

    public function __construct(
        CurrentUser $currentUser,
        OrigenDatosManager $origenDatosManager,
        FormFactoryInterface $formFactory,
    ) {
        $this->currentUser = $currentUser;
        $this->origenDatosManager = $origenDatosManager;
        $this->formFactory = $formFactory;
    }

    public function __invoke(int $idDescripcion,
                             OrigenDatos $origenDatos,
                             Request $request): array
    { 
        $id = "";
        $errorProceso= "";
        $campos = "";
        $prueba = false;
        if (!empty($origenDatos->getId())){
            $origenDatosDto = OrigenDatosDto::createFromOrigenDatos($origenDatos);         
            if ($origenDatosDto->tipoOrigen == TipoOrigenDatosEnum::URL ) {
                $origenDatosDto->url = $origenDatosDto->data;
            } else {
                $origenDatosDto->data = "";
            }
            $form = $this->formFactory->create(OrigenDatosUrlFormType::class, $origenDatosDto);
            $id = $origenDatos->getId();
        } else {
            $form = $this->formFactory->create(OrigenDatosUrlFormType::class); 
        }
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $origenDatosDto = $form->getData(); 
            $prueba = ($origenDatosDto->modoFormulario==ModoFormularioOrigenEnum::Test);
            if ($form->isValid()) {
                $origenDatos->setIdDescripcion($idDescripcion);
                $origenDatos->setTipoOrigen($origenDatosDto->tipoOrigen);
                $data = $origenDatosDto->url;
                $origenDatos->setData($data);

                $username = $this->currentUser->getCurrentUser()->getUsername();
                $origenDatos->setUsuario($username);
                $origenDatos->setSesion($request->getSession()->getId());
                $origenDatos->updatedTimestamps();
                $origenDatos->setCampos("");
                if (empty($origenDatosDto->id)){
                    if ($prueba) {
                        $request->getSession()->set("urlRequest", $origenDatosDto->url);
                        [$origenDatos,$errorProceso] = $this->origenDatosManager->PruebaData($origenDatos,$request->getSession());  
                    } else {
                        $urlRequest =  $request->getSession()->get("urlRequest","");
                        if ($origenDatosDto->url != $urlRequest) {
                            $errorProceso =  "La url comprobada y la enviada han de ser la misma.";
                        } else {
                            [$origenDatos,$errorProceso] = $this->origenDatosManager->createData($origenDatos,$request->getSession()); 
                        }
                        $request->getSession()->remove("urlRequest"); 
                    }
                 } else {
                    if ($prueba) {
                        $request->getSession()->set("urlRequest", $origenDatosDto->url);
                        [$origenDatos,$errorProceso] = $this->origenDatosManager->PruebaData($origenDatos,$request->getSession());  
                    } else {
                        $urlRequest =  $request->getSession()->get("urlRequest","");
                        if ($origenDatosDto->url != $urlRequest) {
                            $errorProceso = "La url comprobada y la enviada han de ser la misma.";
                        } else {
                            [$origenDatos,$errorProceso] = $this->origenDatosManager->saveData($origenDatos,$request->getSession());
                        }   
                        $request->getSession()->remove("urlRequest"); 
                    }     
                }
                if ($origenDatos != null) {
                    $campos = $origenDatos->getCampos();
                    $id = $origenDatos->getId();
                }
            }
        }  
        return [$form, $campos, $id, $prueba , $errorProceso];
    }
}