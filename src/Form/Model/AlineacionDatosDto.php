<?php

namespace App\Form\Model;

use App\Entity\OrigenDatos;

/*
 * Descripción: Es la clase dto de la entidad de la alineacion del conjunto de datos. 
 *              Es el objeto que recoge los datos de los formularios              
 */
class AlineacionDatosDto {
    
    public $id;
    public $idDescripcion;
    public $campos;
    public $alineacionEntidad;
    public $alineacionRelaciones; 
    public $usuario;
    public $sesion;
    public $alineacionEntidades;

    public function __construct()
    {
       $this->modoFormulario = "";
       $this->alineacionEntidades = array();
    }

    public static function createFromAlineacionDatos(OrigenDatos $origenDatos): self
    {
        $dto = new self();
        $dto->id = $origenDatos->getId(); 
        $dto->alineacionEntidad = $origenDatos->getAlineacionEntidad();
        $dto->alineacionRelaciones = $origenDatos->getAlineacionRelaciones();
        $dto->campos = $origenDatos->getCampos();
        $dto->usuario = $origenDatos->getUsuario();
        $dto->sesion = $origenDatos->getSesion();
        return $dto;
    }
}
