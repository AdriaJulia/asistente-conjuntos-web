<?php

namespace App\Service\Manager; 

use App\Entity\DescripcionDatos;
use App\Service\RestApiLocal\RestApiClientDescripcion;

/*
 * Descripción: Es el repositorio de la descripcion de los datos
 *              las operaciones de persistencia las realiza a traves de llamadas apirest
 *              creadas por su correspondiete utilidad de llamadas http 
*/
class DescripcionDatosManager
{

    private $ra;

    public function __construct(RestApiClientDescripcion $ra)
    {
        $this->ra = $ra;
    }

    public function get(int $pagina=0,int $tamano=0, $sesion=null): ?array
    {
        $request = $this->ra->getDescripciondatos($pagina, $tamano, $sesion); 
        return $request['data'];
    }

    public function find(int $id, $sesion): ?DescripcionDatos
    {
        $request = $this->ra->getDescripciondatosId($id,$sesion);
        $des = new DescripcionDatos();
        return $des->getFromArray($request['data']);
    }

    public function new(): DescripcionDatos
    {
        $descripcionDatos = new DescripcionDatos();
        return $descripcionDatos;
    }


    public function create($descripcion, $sesion): DescripcionDatos
    {
        $request = $this->ra->createDescripciondatos($descripcion, $sesion);
        $des = new DescripcionDatos();
        return $des->getFromArray($request['data']);
    }

    public function save($descripcion, $sesion): DescripcionDatos
    {
        $request = $this->ra->updateDescripciondatos($descripcion, $sesion);
        $des = new DescripcionDatos();
        return $des->getFromArray($request['data']);
    }

    public function delete($id, $sesion)
    {
        $request = $this->ra->deleteDescripciondatos($id, $sesion);
    }


    public function saveWorkflow($descripcion, $sesion): DescripcionDatos
    {
        $request = $this->ra->updateWorkflowDescripciondatos($descripcion, $sesion);
        $des = new DescripcionDatos();
        return $des->getFromArray($request['data']);
    }


}