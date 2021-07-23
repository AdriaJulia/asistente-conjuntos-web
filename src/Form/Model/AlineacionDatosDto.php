<?php

namespace App\Form\Model;

use App\Entity\OrigenDatos;

/*
 * Descripción: Es la clase dto de la entidad de la alineación del conjunto de datos. 
 *              Es el objeto que recoge los datos de los formularios              
 */
class AlineacionDatosDto {
    
    public $id;
    public $idDescripcion;
    public $campos;
    public $alineacionEntidad;
    public $subtipoEntidad;
    public $alineacionRelaciones; 
    public $tipoAlineacion;
    public $alineacionXml; 
    public $usuario;
    public $sesion;
    public $alineacionEntidades;
    public $descripcionEntidad;


    public function __construct()
    {
       $this->modoFormulario = "";
       $this->alineacionEntidades = array();
    }

    public static function createFromAlineacionDatos(OrigenDatos $origenDatos): self
    {
        $dto = new self();
        $dto->id = $origenDatos->getId(); 
        $dto->tipoAlineacion = $origenDatos->getTipoAlineacion();
        $dto->alineacionEntidad = $origenDatos->getAlineacionEntidad();
        $dto->subtipoEntidad =  $origenDatos->getSubtipoEntidad();
        $dto->alineacionRelaciones = $origenDatos->getAlineacionRelaciones();
        $dto->alineacionXml = $origenDatos->getAlineacionXml();
        $dto->campos = $origenDatos->getCampos();
        $dto->usuario = $origenDatos->getUsuario();
        $dto->sesion = $origenDatos->getSesion();
        return $dto;
    }
}
