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

/*
 * Descripción: Clase que realiza el trabajo de validar y enviar los datos al repositorio corespondiente
 *              Controla la validacion del formulario y serializa el Dto a la clase entidad
 *              Envía los datos a su persistencia a traves de repositorio  
 *              La clase se crea para el formulario origen de datos en su version de base datostanto el test como el guardado
*/
class OrigenDatosDataBaseFormProcessor
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
            // creo el formulario vacío con los datos actuales
            $form = $this->formFactory->create(OrigenDatosDataBaseFormType::class, $origenDatosDto);
            $id = $origenDatos->getId();
        } else {
            // creo el formulario vacío 
            $form = $this->formFactory->create(OrigenDatosDataBaseFormType::class); 
        }
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $origenDatosDto = $form->getData(); 
            if ($form->isValid()) {
                // recojo si es modo prueba
                $prueba = ($origenDatosDto->modoFormulario==ModoFormularioOrigenEnum::Test);
                 //recojo los datos del formulario
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