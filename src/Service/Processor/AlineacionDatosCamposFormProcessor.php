<?php

namespace App\Service\Processor;

use App\Enum\TipoAlineacionEnum;
use App\Service\Controller\ToolController;
use App\Service\Processor\Tool\OntologiasAlineacionTool;
use App\Enum\ModoFormularioAlineacionEnum;
use App\Enum\EstadoAltaDatosEnum;
use App\Enum\EstadoDescripcionDatosEnum;
use App\Form\Type\AlineacionDatosCamposFormType;
use App\Form\Model\AlineacionDatosDto;
use App\Entity\OrigenDatos;
use App\Service\Manager\AlineacionDatosManager;
use App\Service\Manager\DescripcionDatosManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

/*
 * Descripción: Clase que realiza el trabajo de validar y enviar los datos al repositorio corespondiente
 *              Controla la validación del formulario y serializa el Dto a la clase entidad
 *              Envía los datos a su persistencia a través de repositorio  
 *              La clase se crea para el formulario de alineación Paso 4 por campos
*/
class AlineacionDatosCamposFormProcessor
{
    private $alineacionDatosManager;
    private $descripcionDatosManager;
    private $formFactory;
    private $ontologiasAlineacionTool;
    private $toolController;

    public function __construct(
        AlineacionDatosManager $alineacionDatosManager,
        DescripcionDatosManager $descripcionDatosManager,
        FormFactoryInterface $formFactory,
        OntologiasAlineacionTool $ontologiasAlineacionTool,
        ToolController $toolController
    ) {
        $this->alineacionDatosManager = $alineacionDatosManager;
        $this->descripcionDatosManager = $descripcionDatosManager;
        $this->ontologiasAlineacionTool = $ontologiasAlineacionTool;
        $this->toolController = $toolController;
        $this->formFactory = $formFactory;
    }

    public function __invoke(int $idDescripcion,
                             OrigenDatos $origenDatos,
                             bool $esAdminitrador,
                             Request $request): array
    { 

        if (empty($request->getSession()->getId())) {
            session_start(); 
        }

        $id = "";
        $errorProceso= "";
        $campos = "";
        $prueba = false; 
        $descripcionOntologia = array();
        $enlaceOntologia = array();;
        //extraigo delorigen de datos los campos para hacer el formulario
        $campos = explode(";",$origenDatos->getCampos());
        $alineacionDatosDto = AlineacionDatosDto::createFromAlineacionDatos($origenDatos);
        //si el origen de datos actual no es nuevo
        if (!empty($origenDatos->getId())){
            //si no tengo ontologias 
            if (empty($_POST['alineacionEntidad'])){
                //cargo las opciones sin ontologias
                $options  = array('allowed_campos' => $campos, 
                                  'allowed_ontologias'=>array(),
                                  'allowed_subentidades'=>array(),
                                  'allowed_subentidad'=>array(""),
                                  'allowed_decripcion'=> array(""),
                                  'allowed_enlace'=> array(""));
            } else {
                //cargo las opciones con ontologias y los campos del origen de datos
                $ontologias = $this->ontologiasAlineacionTool->GetOntologia($_POST['alineacionEntidad']); 
                $subEntidades = $this->ontologiasAlineacionTool->GetSubEntidades($_POST['alineacionEntidad']); 
                [$descripcionOntologia,$enlaceOntologia] =  $this->ontologiasAlineacionTool->GetDescricionEnlaceOntologia($_POST['alineacionEntidad']);
                $subEntidad = empty($origenDatos->getSubtipoEntidad()) ? [""] : [$origenDatos->getSubtipoEntidad()];
                $options  = array('allowed_campos' => $campos, 
                                  'allowed_ontologias'=>$ontologias,
                                  'allowed_subentidades'=>$subEntidades,
                                  'allowed_subentidad'=>$subEntidad,
                                  'allowed_decripcion'=>$descripcionOntologia,
                                  'allowed_enlace'=>$enlaceOntologia);
            }
            //tomo los campos alineados del registro actual
            $camposAlineados = (!empty($alineacionDatosDto->alineacionRelaciones))  ? 
                                 get_object_vars(json_decode(str_replace(",}","}",$alineacionDatosDto->alineacionRelaciones))) : 
                                    array();
            //Elimino de la alineacion los  campos que no existan en los actuales del origen de datos
            //Esto es posible si se cambia el origen de los datos.
            $alineacionDatosDto->alineacionRelaciones =   $this->toolController->getNuevaAlineacion($origenDatos->getCampos(),$camposAlineados);
            //cargo el formulario con los campos y las ontologias
            $form = $this->formFactory->create(AlineacionDatosCamposFormType::class, $alineacionDatosDto, $options);
        } else {
            $options  = array('allowed_campos' => "", 
                              'allowed_ontologias'=>array(),
                              'allowed_subentidades'=>array(),
                              'allowed_subentidad'=>array(""),
                              'allowed_decripcion'=> array(""),
                              'allowed_enlace'=> array(""));
            $form = $this->formFactory->create(AlineacionDatosCamposFormType::class,$alineacionDatosDto, $options);
        }
        $form->handleRequest($request);
        $modoFormulario = $alineacionDatosDto->modoFormulario;
        if ($form->isSubmitted()) {
             //recojo los datos del formulario
            $alineacionDatosDto = $form->getData(); 
            //el formulario se puede guradar /omitir / o que muestre las selección de campos
            // esto se envía desde un campo oculto
            $guardar = ($modoFormulario== ModoFormularioAlineacionEnum::Guardar);
            $omitir = ($modoFormulario== ModoFormularioAlineacionEnum::Omitir);
            $seleccion = ($modoFormulario==ModoFormularioAlineacionEnum::Seleccion);
            
            if ($form->isValid()) { 
                //si guardar  informo el objeto y lo envío a guradar a la apirest
                if ($guardar) {
                    $origenDatos->setTipoAlineacion(TipoAlineacionEnum::CAMPOS);
                    $origenDatos->setAlineacionEntidad($alineacionDatosDto->alineacionEntidad);
                    $origenDatos->setAlineacionRelaciones(base64_encode($alineacionDatosDto->alineacionRelaciones));
                    $origenDatos->setSubtipoEntidad(base64_encode($alineacionDatosDto->subtipoEntidad));
                    $origenDatos->setNombreOriginalFile("");
                    $origenDatos->setAlineacionXml("");
                    $origenDatos->setSesion($request->getSession()->getId());
                    $origenDatos->updatedTimestamps();
                    [$origenDatos,$errorProceso] = $this->alineacionDatosManager->saveAlineacionDatosEntidad($origenDatos,$request->getSession());                  
                } else if ($omitir) {
                    $origenDatos->setTipoAlineacion("");
                    $origenDatos->setAlineacionEntidad("");
                    $origenDatos->setAlineacionRelaciones("");
                    $origenDatos->setSubtipoEntidad("");
                    $origenDatos->setNombreOriginalFile("");
                    $origenDatos->setAlineacionXML("");
                    $origenDatos->setSesion($request->getSession()->getId());
                    $origenDatos->updatedTimestamps();
                    [$origenDatos,$errorProceso] = $this->alineacionDatosManager->saveAlineacionDatosEntidad($origenDatos,$request->getSession()); 
                }

                //ademas si el usuario omite el paso envío el workflow
                if ($omitir || $guardar) {
                    $descripcionDatos = $this->descripcionDatosManager->find($idDescripcion,$request->getSession());
                    $descripcionDatos->setSesion($request->getSession()->getId());
                    $descripcionDatos->setEstado(EstadoDescripcionDatosEnum::EN_ESPERA_VALIDACION);
                    if ($esAdminitrador) {
                        $descripcionDatos->setDescripcion("***SIN_CORREO***");
                    } else {
                        $descripcionDatos->setDescripcion("");
                    }
                    [$descripcionDatos,$errorProceso] = $this->descripcionDatosManager->saveWorkflow($descripcionDatos,$request->getSession()); 
                } 
            } 
            $descripcionDatos = $this->descripcionDatosManager->find($idDescripcion, $request->getSession());
            $descripcionDatos->setEstadoAlta(EstadoAltaDatosEnum::ALINEACION);
            $descripcionDatos->updatedTimestamps();
            $this->descripcionDatosManager->save($descripcionDatos,$request->getSession());
        }
        return [$form, $modoFormulario, $origenDatos, $errorProceso];
    }  
}