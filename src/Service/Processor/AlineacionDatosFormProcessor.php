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

/*
 * Descripción: Clase que realiza el trabajo de validar y enviar los datos al repositorio corespondiente
 *              Controla la validacion del formulario y serializa el Dto a la clase entidad
 *              Envía los datos a su persistencia a traves de repositorio  
 *              La clase se crea para el formulario de alineación Paso3 
*/
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
        //extraigo delorigen de datos los campos para hacer el formulario
        $campos = explode(";",$origenDatos->getCampos());
        $alineacionDatosDto = AlineacionDatosDto::createFromAlineacionDatos($origenDatos);
        //si el origen de datos actual no es nuevo
        if (!empty($origenDatos->getId())){
            //si no tengo ontologias 
            if (empty($_POST['alineacionEntidad'])){
                //cargo las opciones sin ontologias
                $options  = array('allowed_campos' => $campos, 'allowed_ontologias'=>array());
            } else {
                //cargo las opciones con ontologias y los campos del origen de datos
                $ontologias = $this->clientHttprest->GetOntologia($_POST['alineacionEntidad']);
                $options  = array('allowed_campos' => $campos, 'allowed_ontologias'=>$ontologias);
            }
            //cargo el formulario con los campos y las ontologias
            $form = $this->formFactory->create(AlineacionDatosFormType::class, $alineacionDatosDto, $options);
        }
        $form->handleRequest($request);
        $modoFormulario = $alineacionDatosDto->modoFormulario;
        if ($form->isSubmitted()) {
             //recojo los datos del formulario
            $alineacionDatosDto = $form->getData(); 
            //el formulario se puede guradar /omitir / o que muestre las seleción de campos
            // esto se envía desde un campo oculto
            $guardar = ($modoFormulario== ModoFormularioAlineacionEnum::Guardar);
            $omitir = ($modoFormulario== ModoFormularioAlineacionEnum::Omitir);
            $seleccion = ($modoFormulario==ModoFormularioAlineacionEnum::Seleccion);
            
            if ($form->isValid()) { 
                //si guardar  informo el objeto y lo envío a guradar a la apirest
                if ($guardar) {
                    $origenDatos->setAlineacionEntidad($alineacionDatosDto->alineacionEntidad);
                    $origenDatos->setAlineacionRelaciones(base64_encode($alineacionDatosDto->alineacionRelaciones));
                    $origenDatos->setSesion($request->getSession()->getId());
                    $origenDatos->updatedTimestamps();
                    [$origenDatos,$errorProceso] = $this->alineacionDatosManager->saveAlineacionDatosEntidad($origenDatos,$request->getSession());                  
                } 
                //ademas si el usuario omite el paso envío el workflow
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