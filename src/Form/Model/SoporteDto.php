<?php

namespace App\Form\Model;

/*
 * Descripción: Es la clase dto del soporte de datos del conjunto de datos. 
 *              Es el objeto que recoge los datos de los formularios              
 */
class SoporteDto {
    public $tipoPeticion;
    public $titulo;
    public $descripcion;
    public $nombre;
    public $emailContacto;
    public $emailContacto2;

    public function __construct()
    {

    }

    public function toJsonData() : string {
      return "{  
                \"tipoPeticion\":\"{$this->tipoPeticion}\",
                \"titulo\":\"{$this->titulo}\",
                \"descripcion\":\"{$this->descripcion}\",
                \"nombre\":\"{$this->nombre}\",
                \"emailContacto\":\"{$this->emailContacto}\"
             }";
    }
}
