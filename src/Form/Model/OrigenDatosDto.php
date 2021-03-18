<?php

namespace App\Form\Model;

use App\Entity\OrigenDatos;

/*
 * Descripción: Es la clase dto de la entidad de origen de datos del conjunto de datos. 
 *              Es el objeto que recoge los datos de los formularios              
 */

class OrigenDatosDto {
    public $id;
    public $idDescripcion;
    public $tipoOrigen;
    public $data;
    public $tipoBaseDatos;
    public $host;
    public $puerto;
    public $servicio;
    public $esquema;
    public $tabla;
    public $usuarioDB;
    public $contrasenaDB; 
    public $usuario;
    public $sesion;
    public $url;
    public $archivo;
    public $campos;
    public $modoFormulario;

    public function __construct()
    {
       $this->modoFormulario = "";
    }

    public static function createFromOrigenDatos(OrigenDatos $origenDatos): self
    {
        $dto = new self();
        $dto->id = $origenDatos->getId();
        $dto->tipoOrigen = $origenDatos->getTipoOrigen();
        $dto->data = $origenDatos->getData();
        $dto->tipoBaseDatos = $origenDatos->getTipoBaseDatos();
        $dto->host = $origenDatos->getHost();
        $dto->puerto = $origenDatos->getPuerto();
        $dto->servicio = $origenDatos->getServicio();
        $dto->esquema = $origenDatos->getEsquema();
        $dto->tabla = $origenDatos->getTabla();
        $dto->usuarioDB = $origenDatos->getUsuarioDB();
        $dto->contrasenaDB = $origenDatos->getContrasenaDB();
        $dto->campos = $origenDatos->getCampos();
        $dto->usuario = $origenDatos->getUsuario();
        $dto->sesion = $origenDatos->getSesion();
        return $dto;
    }
}
