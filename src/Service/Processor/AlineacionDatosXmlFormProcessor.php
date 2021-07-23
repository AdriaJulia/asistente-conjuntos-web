<?php

namespace App\Service\Processor;

use App\Enum\TipoAlineacionEnum;
use App\Enum\ModoFormularioAlineacionEnum;
use App\Enum\EstadoDescripcionDatosEnum;
use App\Form\Type\AlineacionDatosXmlFormType;
use App\Form\Model\AlineacionDatosDto;
use App\Entity\OrigenDatos;
use App\Enum\EstadoAltaDatosEnum;
use App\Service\Manager\AlineacionDatosManager;
use App\Service\Manager\DescripcionDatosManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

/*
 * Descripción: Clase que realiza el trabajo de validar y enviar los datos al repositorio corespondiente
 *              Controla la validación del formulario y serializa el Dto a la clase entidad
 *              Envía los datos a su persistencia a través de repositorio  
 *              La clase se crea para el formulario de alineación Paso 4, por xml
*/
class AlineacionDatosXmlFormProcessor
{
    private $alineacionDatosManager;
    private $descripcionDatosManager;
    private $formFactory;


    public function __construct(
        AlineacionDatosManager $alineacionDatosManager,
        DescripcionDatosManager $descripcionDatosManager,
        FormFactoryInterface $formFactory
    ) {
        $this->alineacionDatosManager = $alineacionDatosManager;
        $this->descripcionDatosManager = $descripcionDatosManager;
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
    
        $errorProceso= "";
        //extraigo delorigen de datos los campos para hacer el formulario
        $campos = explode(";",$origenDatos->getCampos());
        $alineacionDatosDto = AlineacionDatosDto::createFromAlineacionDatos($origenDatos);
        //si el origen de datos actual no es nuevo
        if (!empty($origenDatos->getId())){
            //cargo el formulario con los campos y las ontologias
            $form = $this->formFactory->create(AlineacionDatosXmlFormType::class, $alineacionDatosDto);
        } else {
            $form = $this->formFactory->create(AlineacionDatosXmlFormType::class);
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
                    $brochureFile = $form->get('archivo')->getData();
                    $originalName = $brochureFile->getClientOriginalName();
                    $origenDatos->setAlineacionEntidad("");
                    $origenDatos->setAlineacionRelaciones("");
                    $origenDatos->setSubtipoEntidad("");
                    $origenDatos->setTipoAlineacion(TipoAlineacionEnum::XML);
                    $origenDatos->setAlineacionXml(base64_encode($alineacionDatosDto->alineacionXml));
                    $origenDatos->setNombreOriginalFile($originalName);
                    $origenDatos->setSesion($request->getSession()->getId());
                    $origenDatos->updatedTimestamps();
                    [$origenDatos,$errorProceso] = $this->alineacionDatosManager->saveAlineacionDatosEntidad($origenDatos,$request->getSession());                  
                } else if ($omitir) {
                    $origenDatos->setAlineacionEntidad("");
                    $origenDatos->setAlineacionRelaciones("");
                    $origenDatos->setSubtipoEntidad("");
                    $origenDatos->setTipoAlineacion("");
                    $origenDatos->setAlineacionXML("");
                    $origenDatos->setNombreOriginalFile("");
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