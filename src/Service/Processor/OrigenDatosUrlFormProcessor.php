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

/*
 * Descripción: Clase que realiza el trabajo de validar y enviar los datos al repositorio corespondiente
 *              Controla la validacion del formulario y serializa el Dto a la clase entidad
 *              Envía los datos a su persistencia a traves de repositorio  
 *              La clase se crea para el formulario origen de datos en su version de url el test como el guardado
*/
class OrigenDatosUrlFormProcessor
{
    private $currentUser;
    private $origenDatosManager;
    private $formFactory;

    public function __construct(
        CurrentUser $currentUser,
        OrigenDatosManager $origenDatosManager,
        FormFactoryInterface $formFactory
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
        //si el origen de datos actual no es nuevo
        if (!empty($origenDatos->getId())){
            //inicializo con el origen de datos
            $origenDatosDto = OrigenDatosDto::createFromOrigenDatos($origenDatos); 
             //si el origen de datos es URL      
            if ($origenDatosDto->tipoOrigen == TipoOrigenDatosEnum::URL ) {
                //cargo la url a data
                $origenDatosDto->url = $origenDatosDto->data;
            } else {
                //borro la url a data
                $origenDatosDto->data = "";
            }
            // creo el formulario vacío con los datos actuales
            $form = $this->formFactory->create(OrigenDatosUrlFormType::class, $origenDatosDto);
            $id = $origenDatos->getId();
        } else {
             // creo el formulario vacío 
            $form = $this->formFactory->create(OrigenDatosUrlFormType::class); 
        }
        $form->handleRequest($request);
        //el formulario se ha enviado estoy recogiendo datos
        if ($form->isSubmitted()) {
            $origenDatosDto = $form->getData(); 
            // recojo si es modo prueba
            $prueba = ($origenDatosDto->modoFormulario==ModoFormularioOrigenEnum::Test);
            if ($form->isValid()) {
                //recojo los datos del formulario
                $origenDatos->setIdDescripcion($idDescripcion);
                $origenDatos->setTipoOrigen($origenDatosDto->tipoOrigen);
                $data = $origenDatosDto->url;
                $origenDatos->setData($data);
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