<?php

namespace App\Service\Processor;

use App\Enum\ModoFormularioOrigenEnum;
use App\Enum\TipoOrigenDatosEnum;
use App\Form\Type\OrigenDatosFileFormType;
use App\Form\Model\OrigenDatosDto;
use App\Entity\OrigenDatos;
use App\Service\Manager\OrigenDatosManager;
use Symfony\Component\Form\FormFactoryInterface;
use App\Service\CurrentUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class OrigenDatosFileFormProcessor
{
    private $currentUser;
    private $origenDatosManager;
    private $formFactory;
    private $params;

    public function __construct(
        CurrentUser $currentUser,
        OrigenDatosManager $origenDatosManager,
        ContainerBagInterface $params,
        FormFactoryInterface $formFactory,
    ) {
        $this->currentUser = $currentUser;
        $this->origenDatosManager = $origenDatosManager;
        $this->formFactory = $formFactory;
        $this->params = $params;
    }

    public function __invoke(int $idDescripcion,
                             OrigenDatos $origenDatos,
                             Request $request): array
    { 
        $id = "";
        $errorProceso= "";
        $campos = "";
        $prueba = false;
        $archivoActual = "";
        if (!empty($origenDatos->getId())){
            $origenDatosDto = OrigenDatosDto::createFromOrigenDatos($origenDatos);
            if ($origenDatosDto->tipoOrigen == TipoOrigenDatosEnum::ARCHIVO ) {
                $origenDatosDto->archivo = $origenDatosDto->data;
                $host_restapi = $this->params->get('host_restapi');
                $archivoActual = "{$host_restapi}/storage/default/{$origenDatos->getData()}"; 
            } else {
                $origenDatosDto->data = "";
            }
            $form = $this->formFactory->create(OrigenDatosFileFormType::class, $origenDatosDto);
            $id = $origenDatos->getId();
        } else {
            $form = $this->formFactory->create(OrigenDatosFileFormType::class, null); 
        }
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $base64 = "";
            $origenDatosDto = $form->getData();  
            $prueba = ($origenDatosDto->modoFormulario==ModoFormularioOrigenEnum::Test);
            $request->getSession()->get("fileRequest","");
            $host_restapi = $this->params->get('host_restapi');
            $fileProbado =  $request->getSession()->get("fileProbado","");
 
            if ($form->isValid()) {    
                $origenDatos->setIdDescripcion($idDescripcion);
                $origenDatos->setTipoOrigen($origenDatosDto->tipoOrigen);
                if (!empty($fileProbado) && !$prueba) {
                    $originalName =  $fileProbado;
                    $ext = explode(".", $originalName);
                    $pos = count($ext) -1;
                    $extesionNombre = $ext[$pos];
                    $fileaB64 = "{$host_restapi}/storage/default/{$fileProbado}";
                } else {
                    $origenDatosDto->archivo = $form->get('archivo')->getData(); 
                    $brochureFile = $form->get('archivo')->getData();
                    $originalName = $brochureFile->getClientOriginalName();
                    $ext = explode(".", $originalName);
                    $pos = count($ext) -1;
                    $extesionNombre = $ext[$pos];
                    $fileaB64  = $brochureFile->getPathName();
                }
    
                if ($extesionNombre) {
                    $mime = "";
                    switch ($extesionNombre) {
                        case 'xml':
                            $mime = "application/xml";
                            break;
                        case 'json':
                            $mime = "application/json";
                            break;
                        case 'x-json':
                            $mime = "application/json";
                            break;
                        case 'csv':
                            $mime =  "text/csv";;
                            break;
                        case 'xls':
                            $mime = "application/xls";
                            break;
                        case 'x-xls':
                            $mime = "application/xls";
                            break;
                    } 
                    if (empty($mime)) {
                        $errorProceso= "Por favor seleccione un archivo de los formatos señados valido";
                    }  else {
                        $file = file_get_contents($fileaB64);     
                        $base64 = 'data:' . $mime . ';base64,' . base64_encode($file);
                        $origenDatos->setData($base64);
                    }
                }
                if (!empty($base64)) {
                    $username =  $this->currentUser->getCurrentUser()->getUsername();
                    $origenDatos->setUsuario($username);
                    $origenDatos->setSesion($request->getSession()->getId());
                    $origenDatos->updatedTimestamps();
                    $origenDatos->setCampos("");
                    if (empty($origenDatosDto->id)){
                        if ($prueba) {
                            [$origenDatos,$errorProceso] = $this->origenDatosManager->PruebaData($origenDatos,$request->getSession()); 
                            $request->getSession()->set("fileProbado", $origenDatos->getData()); 
                            $archivoActual = $origenDatos->getData();
                        } else {
                            [$origenDatos,$errorProceso] = $this->origenDatosManager->createData($origenDatos,$request->getSession());  
                            $request->getSession()->remove("fileProbado"); 
                        }
                    } else {
                        if ($prueba) {
                            [$origenDatos,$errorProceso] = $this->origenDatosManager->PruebaData($origenDatos,$request->getSession());
                            $request->getSession()->set("fileProbado", $origenDatos->getData()); 
                            $archivoActual = $origenDatos->getData();  
                        } else {
                            [$origenDatos,$errorProceso] = $this->origenDatosManager->saveData($origenDatos,$request->getSession());  
                            $request->getSession()->remove("fileProbado"); 
                        }    
                    }
 
                    if ($origenDatos != null) {
                        $campos = $origenDatos->getCampos();
                        $id = $origenDatos->getId();
                    }
                }
                
            }
        }  
        return [$form, $campos, $id, $prueba, $archivoActual ,$errorProceso];
    }
}