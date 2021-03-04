<?php

namespace App\Service\Manager;

use App\Entity\OrigenDatos;
use App\Service\RestApiLocal\RestApiClientAlineacion;


class AlineacionDatosManager
{

    private $ra;

    public function __construct(RestApiClientAlineacion $ra)
    {
        $this->ra = $ra;
    }

    public function new(): OrigenDatos
    {
        $origenDatos = new OrigenDatos();
        return $origenDatos;
    }


    public function find(int $id, $sesion): ?OrigenDatos
    {
        $request = $this->ra->getOrigenDatosId($id, $sesion);
        $des = new OrigenDatos();
        return $des->getFromArray($request['data']);
    }

    public function saveAlineacionDatosEntidad($origen ,$sesion): array
    {
        $origenDatos = null;
        $errorProceso = null;
        $request = $this->ra->updateAlineacionEntidadDatos($origen,$sesion);
        if ($request['statusCode']==202) {
            $des = new OrigenDatos();
            $origenDatos = $des->getFromArray($request['data']);
        } else {
            $errorProceso =$request['data'];
        }
        return [$origenDatos,$errorProceso];
    }

}