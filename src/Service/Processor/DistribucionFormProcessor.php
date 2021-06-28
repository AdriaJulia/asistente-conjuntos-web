<?php

namespace App\Service\Processor;


use App\Enum\EstadoAltaDatosEnum;
use App\Enum\EstadoDescripcionDatosEnum;


use App\Form\Type\DistribucionFormType;
use App\Service\RestApiLocal\RestApiClientDescripcion;

use App\Service\Manager\DescripcionDatosManager;

use Symfony\Component\Form\FormFactoryInterface;
use App\Service\CurrentUser;

use App\Service\Processor\Tool\ProcessorTool; 

use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;

/*
 * Descripción: Clase que realiza el trabajo de validar y enviar los datos al repositorio corespondiente
 *              Controla la validacion del formulario y serializa el Dto a la clase entidad
 *              Envía los datos a su persistencia a traves de repositorio  
 *              La clase se crea para el formulario de descripcion de datos Paso1.1 
*/
class DistribucionFormProcessor
{
    private $currentUser;
    private $descripcionDatosManager;
    private $formFactory;
    private $restApiClientDescripcion;
    private $logger;

    public function __construct(
        CurrentUser $currentUser,
        DescripcionDatosManager $descripcionDatosManager,
        FormFactoryInterface $formFactory,
        RestApiClientDescripcion $restApiClientDescripcion,
        LoggerInterface $logger
    ) {
        $this->currentUser = $currentUser;
        $this->descripcionDatosManager = $descripcionDatosManager;
        $this->formFactory = $formFactory;
        $this->restApiClientDescripcion = $restApiClientDescripcion;
        $this->logger = $logger;
    }

    public function __invoke(Request $request): array
    { 
        //recojo las distribuciones para asociarlas al formulario
        $distribuciones =  $this->descripcionDatosManager->get(1,0,$request->getSession());
        $distribucionClonada = null;
        $nueva = false;
        //inicilizo con nueva para dar la opcion de nueva distribución
        $seleccion["#nueva#"] = "--Nuevo conjunto de datos--";
        foreach($distribuciones['data'] as $distribucion)
        {
            if ($distribucion['distribucion']<=0) {
                $seleccion[$distribucion['id']] = $distribucion['titulo'];
            }
        }
        asort($seleccion);
        //creo el formulario
        $options  = array('allowed_distribuciones' => $seleccion);
        $form = $this->formFactory->create(DistribucionFormType::class,null,$options); 

        $distribucionSeleccionada = array();
        $form->handleRequest($request);
        //creo el formulario si se ha lanzado con éxito recojo la distribucion 
        if ($form->isSubmitted() && $form->isValid()) {
            $distribucionSeleccionada = $request->get('distribuciones');  
            $nueva = ($distribucionSeleccionada=="#nueva#");
            if (!$nueva) {
                $distribucionClonada = $this->descripcionDatosManager->clone($distribucionSeleccionada,$request->getSession());
            }
        } 
        return [$form,$distribucionClonada,$nueva];
    }
}
