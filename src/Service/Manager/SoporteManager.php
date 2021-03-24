<?php

namespace App\Service\Manager;

use App\Form\Model\SoporteDto;
use App\Service\RestApiLocal\RestApiSoporte;

/*
 * Descripción: Es el repositorio del envío mail soporte d
 *              las operaciones de envío las realiza a traves de llamadas apirest
 *              creadas por su correspondiete utilidad de llamadas http 
*/
class SoporteManager
{

    private $ra;
    public function __construct(RestApiSoporte $ra)
    {
        $this->ra = $ra;
    }


    public function envia(SoporteDto $soporte, $sesion ) : array
    {
        $datos = null;
        $errorProceso = null;
        $request = $request = $this->ra->envia($soporte, $sesion);
        if ($request['statusCode']==201) {
            $datos = $request['data']; 
        } else {
            $errorProceso =$request['data'];
        }
        return [$datos, $errorProceso];;
    }

}