<?php

namespace App\Form\Model;

use App\Entity\DescripcionDatos;

/*
 * Descripción: Es la clase dto de la entidad de la descripción del conjunto de datos. 
 * 
 */
class DescripcionDatosDto {
    public $id;
    public $titulo;
    public $identificacion;
    public $descripcion;
    public $frecuenciaActulizacion;
    public $fechaInicio;
    public $fechaFin;
    public $coberturaGeografica;
    public $publicador;
    public $tematica;
    public $vocabularios;
    public $descripcionVocabularios;
    public $diccionarioDatos;
    public $servicios;
    public $descripcionServicios;
    public $calidaDdato;
    public $etiquetas;
    public $coberturaIdioma;
    public $idiomas;
    public $estructura;
    public $nivelDetalle;
    public $licencias;
    public $distribucion;
    public $usuario;
    public $sesion;
    public $estado;
    public $estadoAlta;
    public $coberturasGeograficas;
    public $coberturaTemporal;
    public $porcesaAdo;

    public function __construct()
    {
        $this->coberturasGeograficas =  array();
        $this->coberturaTemporal =  array();
        $this->coberturaIdioma = array();
        $this->diccionarioDatos = array();
        $this->calidadDato = array();
        $this->estadoAlta = "";
    }

    public static function createFromDescripcionDatos(DescripcionDatos $descripcionDatos): self
    {
        $dto = new self();
        $dto->id = $descripcionDatos->getId();
        $dto->titulo = $descripcionDatos->getTitulo();
        $dto->identificacion = $descripcionDatos->getIdentificacion();
        $dto->descripcion = $descripcionDatos->getDescripcion();
        $dto->frecuenciaActulizacion = $descripcionDatos->getFrecuenciaActulizacion();
       
        $dto->fechaInicio = $descripcionDatos->getFechaInicio();
        $dto->fechaFin = $descripcionDatos->getFechaFin();
        $dto->coberturaTemporal = array('fechaInicio'=> $dto->fechaInicio, 'fechaFin'=> $dto->fechaFin);
        $dto->coberturaGeografica = $descripcionDatos->getCoberturaGeografica();
        $dto->publicador = $descripcionDatos->getPublicador();
        $dto->tematica = $descripcionDatos->getTematica();
        $dto->coberturaIdioma = self::dameIdiomas($descripcionDatos->getIdiomas());
        $dto->vocabularios = $descripcionDatos->getVocabularios();
        $dto->descripcionVocabularios =   $descripcionDatos->getDescripcionVocabularios();
        $dto->diccionarioDatos = array("descripcion" =>  $dto->descripcionVocabularios, "vocabularios"=> $dto->vocabularios);
        $dto->descripcionServicios =   $descripcionDatos->getDescripcionServicios();
        $dto->servicios = $descripcionDatos->getServicios();
        $dto->calidadDato = array("descripcion" =>  $dto->descripcionServicios, "servicios"=> $dto->servicios);
        $dto->etiquetas = $descripcionDatos->getEtiquetas();
        $dto->nivelDetalle = $descripcionDatos->getNivelDetalle();
        $dto->licencias = $descripcionDatos->getLicencias();
        $dto->distribucion = $descripcionDatos->getDistribucion();
        $dto->usuario = $descripcionDatos->getUsuario();
        $dto->sesion = $descripcionDatos->getSesion();
        $dto->estado = $descripcionDatos->getEstado();
        $dto->estadoAlta = $descripcionDatos->getEstadoAlta();
        return $dto;
    }
    
    private static function dameIdiomas($idiomasBd) : array{
        $coverturaIdiomas = array();
        if ($idiomasBd!=null) {
          $idiomas = explode(",",$idiomasBd);
          $lenguajes = array();
          $coverturaIdiomas['otroslenguajes'] = "";
          foreach($idiomas as $idioma){
            if ($idioma == "Español"){
              $lenguajes[0] = $idioma;
            } else if ($idioma == "Inglés"){
              $lenguajes[1] = $idioma;
            } else if ($idioma == "Francés"){
              $lenguajes[2] = $idioma;
            } else if ($idioma == "Lenguas Aragonesas") {
              $lenguajes[3] = $idioma;
            } else {
              $lenguajes[4] = "Otro";
              $coverturaIdiomas['otroslenguajes'] = $idioma;
            }     
          }
          $coverturaIdiomas['lenguajes'] = $lenguajes;
        }
        return $coverturaIdiomas;
    }

}