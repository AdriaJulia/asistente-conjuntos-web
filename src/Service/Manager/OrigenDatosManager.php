<?php

namespace App\Service\Manager;

use App\Entity\OrigenDatos;
use App\Service\RestApiLocal\RestApiClientOrigen;

/*
 * Descripción: Es el repositorio del origen de los datos
 *              las operaciones de persistencia las realiza a traves de llamadas apirest
 *              creadas por su correspondiete utilidad de llamadas http 
*/
class OrigenDatosManager
{

    private $ra;

    public function __construct(RestApiClientOrigen $ra)
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

    
    public function DatosFicha($id ,$sesion): array
    {
        $datos = null;
        $errorProceso = null;
        $campos = "";
        $request = $this->ra->getDatosFichaId($id,$sesion);
        if ($request['statusCode']==200) {
            $datos = $request['data']['data'];
            $campos = $request['data']['campos'];
            $errorProceso =  $request['data']['error_proceso']; 
        } 
        return [$datos, $campos, $errorProceso];
    }

    public function PruebaData($origen ,$sesion): array
    {
        $origenDatos = null;
        $errorProceso = null;
        $request = $this->ra->testOrigenDatosData($origen,$sesion);
        if ($request['statusCode']==201) {
            $des = new OrigenDatos();
            $origenDatos = $des->getFromArray($request['data']);
        } else {
            $errorProceso =$request['data'];
        }
        return [$origenDatos,$errorProceso];
    }


    public function createData($origen ,$sesion): array
    {
        $origenDatos = null;
        $errorProceso = null;
        $request = $this->ra->createOrigenDatosData($origen,$sesion);
        if ($request['statusCode']==201) {
            $des = new OrigenDatos();
            $origenDatos = $des->getFromArray($request['data']);
        } else {
            $errorProceso =$request['data'];
        }
        return [$origenDatos,$errorProceso];
    }


    public function PruebaDataBasedatos($origen, $sesion): array
    {
        $origenDatos = null;
        $errorProceso = null;
        $request = $this->ra->testOrigenDatosDataBasedatos($origen,$sesion);
        if ($request['statusCode']==201) {
            $des = new OrigenDatos();
            $origenDatos = $des->getFromArray($request['data']);
        } else {
            $errorProceso =$request['data'];
        }
        return [$origenDatos,$errorProceso];
    }

    public function createDataBasedatos($origen, $sesion): array
    {
        $origenDatos = null;
        $errorProceso = null;
        $request = $this->ra->createOrigenDatosDataBasedatos($origen,$sesion);
        if ($request['statusCode']==201) {
            $des = new OrigenDatos();
            $origenDatos = $des->getFromArray($request['data']);
        } else {
            $errorProceso =$request['data'];
        }
        return [$origenDatos,$errorProceso];
    }


    public function saveData($origen, $sesion): array
    {
        $origenDatos = null;
        $errorProceso = null;
        $request = $this->ra->updateOrigenDatosData($origen, $sesion);
        if ($request['statusCode']==202) {
            $des = new OrigenDatos();
            $origenDatos = $des->getFromArray($request['data']);
        } else {
            $errorProceso =$request['data'];
        }
        return [$origenDatos,$errorProceso];
    }

    public function saveDataBaseDatos($origen, $sesion): array
    {
        $origenDatos = null;
        $errorProceso = null;
        $request = $this->ra->updateOrigenDatosDataBasedatos($origen, $sesion);
        if ($request['statusCode']==202) {
            $des = new OrigenDatos();
            $origenDatos = $des->getFromArray($request['data']);
        } else {
            $errorProceso =$request['data'];
        }
        return [$origenDatos,$errorProceso];
    }


    public function delete($id, $sesion)
    {
        $request = $this->ra->deleteOrigendatos($id, $sesion);
        $des = new OrigenDatos();
        return $des->getFromArray($request);
    }
}