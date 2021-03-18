<?php

namespace App\Service\Processor;

use App\Enum\ModoFormularioOrigenEnum;
use App\Form\Type\OrigenDatosDataBaseFormType;
use App\Form\Model\OrigenDatosDto;
use App\Entity\OrigenDatos;
use App\Service\Manager\OrigenDatosManager;
use Symfony\Component\Form\FormFactoryInterface;
use App\Service\CurrentUser;
use Symfony\Component\HttpFoundation\Request;


class OrigenDatosDataBaseFormProcessor
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
            $form = $this->formFactory->create(OrigenDatosDataBaseFormType::class, $origenDatosDto);
            $id = $origenDatos->getId();
        } else {
            $form = $this->formFactory->create(OrigenDatosDataBaseFormType::class); 
        }
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $origenDatosDto = $form->getData(); 
            if ($form->isValid()) {
                $prueba = ($origenDatosDto->modoFormulario==ModoFormularioOrigenEnum::Test);
                $origenDatos->setIdDescripcion($idDescripcion);
                $origenDatos->setTipoOrigen($origenDatosDto->tipoOrigen);
                $origenDatos->setTipoBaseDatos($origenDatosDto->tipoBaseDatos);
                $origenDatos->setHost($origenDatosDto->host);
                $origenDatos->setPuerto($origenDatosDto->puerto);
                $origenDatos->setServicio(!empty($origenDatosDto->servicio) ? $origenDatosDto->servicio : "_");
                $origenDatos->setEsquema($origenDatosDto->esquema);
                $origenDatos->setTabla($origenDatosDto->tabla);
                $origenDatos->setUsuarioDB($origenDatosDto->usuarioDB);
                $origenDatos->setContrasenaDB($origenDatosDto->contrasenaDB);  

                $username = $this->currentUser->getCurrentUser()->getUsername();
                $origenDatos->setUsuario($username);
                $origenDatos->setSesion($request->getSession()->getId());
                $origenDatos->updatedTimestamps();
                $origenDatos->setCampos("");
                if (empty($origenDatosDto->id)){
                    if ($prueba) {
                        $request->getSession()->set("dbRequest", $origenDatos->toJsonDatabase());
                        [$origenDatos,$errorProceso] = $this->origenDatosManager->PruebaDataBasedatos($origenDatos,$request->getSession());  
                    } else {
                        $dbRequest = $request->getSession()->get("dbRequest", "");
                        if ($dbRequest != $origenDatos->toJsonDatabase()) {
                            $errorProceso = "la base de datos comprobada y la enviada han de ser el misma.";
                        } else {
                            [$origenDatos,$errorProceso] = $this->origenDatosManager->createDataBasedatos($origenDatos,$request->getSession());  
                        } 
                        $request->getSession()->remove("dbRequest"); 
                    }
                } else {
                    if ($prueba) {
                        $request->getSession()->set("dbRequest", $origenDatos->toJsonDatabase());
                        [$origenDatos,$errorProceso] = $this->origenDatosManager->PruebaDataBasedatos($origenDatos,$request->getSession());  
                    } else {
                        $dbRequest = $request->getSession()->get("dbRequest", "");
                        if ($dbRequest != $origenDatos->toJsonDatabase()) {
                            $errorProceso = "la base de datos comprobada y la enviada han de ser el misma.";
                        } else {
                            [$origenDatos,$errorProceso] = $this->origenDatosManager->saveDataBaseDatos($origenDatos,$request->getSession());     
                        }
                        $request->getSession()->remove("dbRequest");
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