<?php

namespace App\Form\Model;

use App\Entity\DescripcionDatos;

/*
 * Descripción: Es la clase dto de la entidad de la descripción del conjunto de datos. 
 * 
 */
class DescripcionDatosDto {
    public $id;
    public $denominacion;
    public $identificacion;
    public $descripcion;
    public $frecuenciaActulizacion;
    public $fechaInicio;
    public $fechaFin;
    public $territorio;
    public $instancias;
    public $organoResponsable;
    public $finalidad;
    public $condiciones;
    public $vocabularios;
    public $servicios;
    public $etiquetas;
    public $estructura;
    public $estructuraDenominacion;
    public $licencias;
    public $formatos;
    public $usuario;
    public $sesion;
    public $estado;
    public $estadoAlta;
    public $territorios;
    public function __construct()
    {
        $this->territorios =  array();
        $this->estadoAlta = "";
    }

    public static function createFromDescripcionDatos(DescripcionDatos $descripcionDatos): self
    {
        $dto = new self();
        $dto->id = $descripcionDatos->getId();
        $dto->denominacion = $descripcionDatos->getDenominacion();
        $dto->identificacion = $descripcionDatos->getIdentificacion();
        $dto->descripcion = $descripcionDatos->getDescripcion();
        $dto->frecuenciaActulizacion = $descripcionDatos->getFrecuenciaActulizacion();
        $dto->fechaInicio = $descripcionDatos->getFechaInicio();
        $dto->fechaFin = $descripcionDatos->getFechaFin();
        $dto->territorio = $descripcionDatos->getTerritorio();
        $dto->instancias = $descripcionDatos->getInstancias();
        $dto->organoResponsable = $descripcionDatos->getOrganoResponsable();
        $dto->finalidad = $descripcionDatos->getFinalidad();
        $dto->condiciones = $descripcionDatos->getCondiciones();
        $dto->vocabularios = $descripcionDatos->getVocabularios();
        $dto->formatos = $descripcionDatos->getFormatos();
        $dto->servicios = $descripcionDatos->getServicios();
        $dto->etiquetas = $descripcionDatos->getEtiquetas();
        $dto->estructura = $descripcionDatos->getEstructura();
        $dto->estructuraDenominacion = $descripcionDatos->getEstructuraDenominacion();
        $dto->licencias = $descripcionDatos->getLicencias();
        $dto->usuario = $descripcionDatos->getUsuario();
        $dto->sesion = $descripcionDatos->getSesion();
        $dto->estado = $descripcionDatos->getEstado();
        $dto->estadoAlta = $descripcionDatos->getEstadoAlta();
        return $dto;
    }
}