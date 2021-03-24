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

/*
 * Descripción: Clase que realiza el trabajo de validar y enviar los datos al repositorio corespondiente
 *              Controla la validacion del formulario y serializa el Dto a la clase entidad
 *              Envía los datos a su persistencia a traves de repositorio  
 *              La clase se crea para el formulario origen de datos en su version de fichero el test como el guardado
*/
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
        FormFactoryInterface $formFactory
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
         //si el origen de datos actual no es nuevo
        if (!empty($origenDatos->getId())){
            $origenDatosDto = OrigenDatosDto::createFromOrigenDatos($origenDatos);
            //si el origen de datos es ARCHIVO 
            if ($origenDatosDto->tipoOrigen == TipoOrigenDatosEnum::ARCHIVO ) {
                //cargo la url a data
                $origenDatosDto->archivo = $origenDatosDto->data;
                $host_restapi = $this->params->get('host_restapi');
                $archivoActual = "{$host_restapi}/storage/default/{$origenDatos->getData()}"; 
            } else {
                  //borro la url a data
                $origenDatosDto->data = "";
            }
             // creo el formulario vacío con los datos actuales
            $form = $this->formFactory->create(OrigenDatosFileFormType::class, $origenDatosDto);
            $id = $origenDatos->getId();
        } else {
             // creo el formulario vacío 
            $form = $this->formFactory->create(OrigenDatosFileFormType::class, null); 
        }
        $form->handleRequest($request);
            //el formulario se ha enviado estoy recogiendo datos
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
                //si el archivo ya se ha subido y no es una comprobación
                if (!empty($fileProbado) && !$prueba) {
                    $originalName =  $fileProbado;
                    $ext = explode(".", $originalName);
                    $pos = count($ext) -1;
                    $extesionNombre = $ext[$pos];
                    $fileaB64 = "{$host_restapi}/storage/default/{$fileProbado}";
                } else {
                     //si no tengo que subir el archivo y para eso tengo que pasarlo a Base64
                     //el que guarda el archivo apirest
                    $origenDatosDto->archivo = $form->get('archivo')->getData(); 
                    $brochureFile = $form->get('archivo')->getData();
                    $originalName = $brochureFile->getClientOriginalName();
                    $ext = explode(".", $originalName);
                    $pos = count($ext) -1;
                    $extesionNombre = $ext[$pos];
                    $fileaB64  = $brochureFile->getPathName();
                }
                //saco la extension para mandar el archivo por apires en base 64
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
                        //este es el archivo en base64    
                        $base64 = 'data:' . $mime . ';base64,' . base64_encode($file);
                        $origenDatos->setData($base64);
                    }
                }
                if (!empty($base64)) {
                     // esto es para poder hacer los test unitarios sin LDAP
                    if ($this->currentUser->getCurrentUser()!=null){
                        $username = $this->currentUser->getCurrentUser()->getExtraFields()['mail'];
                    } else {
                        $username = "MOCKSESSID";
                    }
                    $origenDatos->setUsuario($username);
                    $origenDatos->setSesion($request->getSession()->getId());
                    $origenDatos->updatedTimestamps();
                    $origenDatos->setCampos("");
                        //ahora distingo si la llamada es de un origen nuevo o existente y prueba o guradar
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