<?php


namespace App\Service\Processor\Tool; 

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Service\Manager\DescripcionDatosManager;
use  App\Service\Processor\Tool\ProcessorTool;
/*
 * Descripción: Recibe todos los identificadores existentes y comprueba que no existan duplicados
 */              
class IdentificadorUnicoTool
{

     private $identificadores = array();

     private $descripcionDatosManager;
     private $sessionInterface;
 
     public function __construct(
         DescripcionDatosManager $descripcionDatosManager,
         SessionInterface $sessionInterface
     ) {
         $this->descripcionDatosManager = $descripcionDatosManager;
         $this->sessionInterface = $sessionInterface;
     }
     

     /***
     * Descripción: Inserta un identificador al array estático de identificados
     *                          
     * Parámetros:
     *             nuevoIdentificador: identificador a insertar en el array      
     */
     public function Inicializa() :void {

          $datos = $this->descripcionDatosManager->get(1,0, $this->sessionInterface);
          foreach($datos['data'] as $dato) {
               if (!in_array($dato['identificacion'], $this->identificadores)) {
                    $count = count($this->identificadores);
                    $this->identificadores[$count] = $dato['identificacion'];
               }
          }
          return;
     }

     /***
     * Descripción: Comprueba si existe el nuevo identificador
     *                         
     * Parámetros:
     *             identificador:  identificador      
     */
    public function ExiteIdentificador($titulo):bool {
       $identificador = ProcessorTool::clean($titulo);
       return in_array($identificador,$this->identificadores); 
    }
}