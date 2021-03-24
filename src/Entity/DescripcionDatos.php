<?php

namespace App\Entity;

use App\Entity\OrigenDatos;
use Doctrine\ORM\Mapping as ORM;


use App\Service\Controller\ToolController;

/*
 * Descripción: Es la clase entidad de la descripcion del conjunto de datos. 
 *              Esta anotada con Doctrine, pero no persite en ninguna BD
 *              WebSite envía todas las operaciones de persistencia via apitest 
 *              que es donde realmente se guardan los datos.
 *              la notacion ORM es debida los formularios validadores y serializadores
 *              
 */
/**
 * @ORM\Entity()
 */
class DescripcionDatos
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $denominacion;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $identificacion;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $descripcion;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $frecuenciaActulizacion;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaInicio;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaFin;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $territorio;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $instancias;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $organoResponsable;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $finalidad;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $condiciones;


     /**
     * @ORM\Column(type="string",length=255, nullable=true)
     */
    private $licencias;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    private $vocabularios;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    private $servicios;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    private $etiquetas;

    /**
     * @ORM\Column(type="text",  nullable=true)
     */
    private $estructura;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $estructuraDenominacion;

    /**
     * @ORM\Column(type="string", length=5120, nullable=true)
     */
    private $formatos;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $usuario;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $estado;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $estadoAlta;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sesion;

    /**
     * @ORM\Column(name="creado_el", type="datetime")
     */
    private $creadoEl;

    /**
     * @ORM\Column(name="actualizado_en", type="datetime")
     */
    private $actualizadoEn; 

    /**
     * @ORM\OneToOne(targetEntity=OrigenDatos::class, mappedBy="descripcionDatos", cascade={"persist"})
     */
    private $origenDatos;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDenominacion(): ?string
    {
        return $this->denominacion;
    }

    public function setDenominacion(?string $denominacion): self
    {
        $this->denominacion = $denominacion;

        return $this;
    }

    public function getIdentificacion(): ?string
    {
        return $this->identificacion;
    }

    public function setIdentificacion(?string $identificacion): self
    {
        $this->identificacion = $identificacion;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getFrecuenciaActulizacion(): ?string
    {
        return $this->frecuenciaActulizacion;
    }

    public function setFrecuenciaActulizacion(?string $frecuenciaActulizacion): self
    {
        $this->frecuenciaActulizacion = $frecuenciaActulizacion;

        return $this;
    }

    public function getFechaInicio(): ?\DateTimeInterface
    {
        return $this->fechaInicio;
    }

    public function setFechaInicio(?\DateTimeInterface $fechaInicio): self
    {
        $this->fechaInicio = $fechaInicio;

        return $this;
    }

    public function getFechaFin(): ?\DateTimeInterface
    {
        return $this->fechaFin;
    }

    public function setFechaFin(?\DateTimeInterface $fechaFin): self
    {
        $this->fechaFin = $fechaFin;

        return $this;
    }

    public function getTerritorio(): ?string
    {
        return $this->territorio;
    }

    public function setTerritorio(?string $territorio): self
    {
        $this->territorio = $territorio;

        return $this;
    }

    public function getInstancias(): ?string
    {
        return $this->instancias;
    }

    public function setInstancias(?string $instancias): self
    {
        $this->instancias = $instancias;

        return $this;
    }

    public function getOrganoResponsable(): ?string
    {
        return $this->organoResponsable;
    }

    public function setOrganoResponsable(?string $organoResponsable): self
    {
        $this->organoResponsable = $organoResponsable;

        return $this;
    }

    public function getFinalidad(): ?string
    {
        return $this->finalidad;
    }

    public function setFinalidad(?string $finalidad): self
    {
        $this->finalidad = $finalidad;

        return $this;
    }

    public function getCondiciones(): ?string
    {
        return $this->condiciones;
    }

    public function setCondiciones(?string $condiciones): self
    {
        $this->condiciones = $condiciones;

        return $this;
    }

    public function getVocabularios(): ?string
    {
        return $this->vocabularios;
    }

    public function setVocabularios(?string $vocabularios): self
    {
        $this->vocabularios = $vocabularios;

        return $this;
    }

    public function getServicios(): ?string
    {
        return $this->servicios; 
    }

    public function setServicios(?string $servicios): self
    {
        $this->servicios = $servicios;

        return $this;
    }

    public function getEtiquetas(): ?string
    {
        return $this->etiquetas;
    }

    public function setEtiquetas(?string $etiquetas): self
    {
        $this->etiquetas = $etiquetas;

        return $this;
    }

    public function getEstructura(): ?string
    {
        return $this->estructura;
    }

    public function setEstructura(?string $estructura): self
    {
        $this->estructura = $estructura;

        return $this;
    }

    public function getEstructuraDenominacion(): ?string
    {
        return $this->estructuraDenominacion;
    }

    public function setEstructuraDenominacion(?string $estructuraDenominacion): self
    {
        $this->estructuraDenominacion = $estructuraDenominacion;

        return $this;
    }

    public function getLicencias(): ?string
    {
        return $this->licencias;
    }

    public function setLicencias(?string $licencias): self
    {
        $this->licencias = $licencias;

        return $this;
    }

    public function getFormatos(): ?string
    {
        return $this->formatos;
    }

    public function setFormatos(?string $formatos): self
    {
        $this->formatos = $formatos;

        return $this;
    }

    public function getUsuario(): ?string
    {
        return $this->usuario;
    }

    public function setUsuario(string $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getSesion(): ?string
    {
        return $this->sesion;
    }

    public function setSesion(string $sesion): self
    {
        $this->sesion = $sesion;

        return $this;
    }
    
    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    public function getEstadoAlta(): ?string
    {
        return $this->estadoAlta;
    }

    public function setEstadoAlta(?string $estadoAlta): self
    {
        $this->estadoAlta = $estadoAlta;

        return $this;
    }

    public function getCreadoEl(): ?\DateTimeInterface
    {
        return $this->creadoEl;
    }

    public function setCreadoEl(\DateTimeInterface $creadoEl): self
    {
        $this->creadoEl = $creadoEl;

        return $this;
    }

    public function getActualizadoEn(): ?\DateTimeInterface
    {
        return $this->actualizadoEn;
    }

    public function setActualizadoEn(\DateTimeInterface $actualizadoEn): self
    {
        $this->actualizadoEn = $actualizadoEn;

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps()
    { 
        $dateTimeNow = new \DateTime('now');
        $this->setActualizadoEn($dateTimeNow);
        if ($this->getCreadoEl() === null) {
            $this->setCreadoEl($dateTimeNow);
        }
    }


    public function TieneOrigenDatos(): bool
    {
        $tieneorigen = false;
        $tieneorigen = ($this->origenDatos != null);
        if ($tieneorigen) {
            $tieneorigen = ($this->origenDatos->getId() != null);
        }
        return  $tieneorigen; 
    }

    public function getOrigenDatos(): ?OrigenDatos
    {
        return $this->origenDatos;
    }

    public function setOrigenDatos(?OrigenDatos $origenDatos): self
    {
        // unset the owning side of the relation if necessary
        if ($origenDatos === null && $this->origenDatos !== null) {
            $this->origenDatos->setDescripcionDatos(null);
        }

        // set the owning side of the relation if necessary
        if ($origenDatos !== null && $origenDatos->getDescripcionDatos() !== $this) {
            $origenDatos->setDescripcionDatos($this);
        }

        $this->origenDatos = $origenDatos;

        return $this;
    }


    public function toJsonCreate() : string {

        $fechaInicio = !empty($this->getFechaInicio()) ? date_format($this->getFechaInicio(),'Y-m-d H:i'):  null;
        $fechaFin =  !empty($this->getFechaFin()) ?  date_format($this->getFechaFin(),'Y-m-d H:i') : null;
        $json = "{";
            $json = !empty($this->getFechaInicio()) ?  $json . "\"fechaInicio\":\"{$fechaInicio}\"," : $json;
            $json = !empty($this->getFechaFin()) ?  $json . "\"fechaFin\":\"{$fechaFin }\"," : $json;
            $json = !empty($this->getDenominacion()) ?  $json . "\"denominacion\":\"{$this->getDenominacion()}\"," : $json;
            $json = !empty($this->getIdentificacion()) ?  $json . "\"identificacion\":\"{$this->getIdentificacion()}\"," : $json;
            $json = !empty($this->getDenominacion()) ?  $json . "\"denominacion\":\"{$this->getDenominacion()}\"," : $json;
            $json = !empty($this->getFrecuenciaActulizacion()) ?  $json . "\"frecuenciaActulizacion\":\"{$this->getFrecuenciaActulizacion()}\"," : $json;
            $json = !empty($this->getDescripcion()) ?  $json . "\"descripcion\":\"{$this->getDescripcion()}\"," : $json;
            $json = !empty($this->getTerritorio()) ?  $json . "\"territorio\":\"{$this->getTerritorio()}\"," : $json;
            $json = !empty($this->getInstancias()) ?  $json . "\"instancias\":\"{$this->getInstancias()}\"," : $json;
         
            $json = $json . "\"usuario\":\"{$this->getUsuario()}\",";
            $json = $json . "\"sesion\":\"{$this->getSesion()}\",";
            $json = $json . "\"estado\":\"{$this->getEstado()}\",";
            $json = $json . "\"estadoAlta\":\"{$this->getEstadoAlta()}\"}";
          return  $json;
    }

    public function toJsonUpdate() : string {
 
        $fechaInicio = !empty($this->getFechaInicio()) ? date_format($this->getFechaInicio(),'Y-m-d H:i'):  null;
        $fechaFin =  !empty($this->getFechaFin()) ?  date_format($this->getFechaFin(),'Y-m-d H:i') : null;
        $json ="{";
            $json = !empty($this->getFechaInicio()) ?  $json . "\"fechaInicio\":\"{$fechaInicio}\"," : $json;
            $json = !empty($this->getFechaFin()) ?  $json . "\"fechaFin\":\"{$fechaFin }\"," : $json;
            $json = !empty($this->getDenominacion()) ?  $json . "\"denominacion\":\"{$this->getDenominacion()}\"," : $json;
            $json = !empty($this->getIdentificacion()) ?  $json . "\"identificacion\":\"{$this->getIdentificacion()}\"," : $json;
            $json = !empty($this->getDenominacion()) ?  $json . "\"denominacion\":\"{$this->getDenominacion()}\"," : $json;
            $json = !empty($this->getFrecuenciaActulizacion()) ?  $json . "\"frecuenciaActulizacion\":\"{$this->getFrecuenciaActulizacion()}\"," : $json;
            $json = !empty($this->getDescripcion()) ?  $json . "\"descripcion\":\"{$this->getDescripcion()}\"," : $json;
            $json = !empty($this->getTerritorio()) ?  $json . "\"territorio\":\"{$this->getTerritorio()}\"," : $json;
            $json = !empty($this->getInstancias()) ?  $json . "\"instancias\":\"{$this->getInstancias()}\"," : $json;
         
            $json = !empty($this->getOrganoResponsable()) ?  $json . "\"organoResponsable\":\"{$this->getOrganoResponsable()}\"," : $json;
            $json = !empty($this->getFinalidad()) ?  $json . "\"finalidad\":\"{$this->getFinalidad()}\"," : $json;
            $json = !empty($this->getCondiciones()) ?  $json . "\"condiciones\":\"{$this->getCondiciones()}\"," : $json;
            $json = !empty($this->getLicencias()) ?  $json . "\"licencias\":\"{$this->getLicencias()}\"," : $json;
            $json = !empty($this->getVocabularios()) ?  $json . "\"vocabularios\":\"{$this->getVocabularios()}\"," : $json;
            $json = !empty($this->getServicios()) ?  $json . "\"servicios\":\"{$this->getServicios()}\"," : $json;
            $json = !empty($this->getEtiquetas()) ?  $json . "\"etiquetas\":\"{$this->getEtiquetas()}\"," : $json;
            $json = !empty($this->getEstructura()) ?  $json . "\"estructura\":\"{$this->getEstructura()}\"," : $json;
            $json = !empty($this->getEstructuraDenominacion()) ?  $json . "\"estructuraDenominacion\":\"{$this->getEstructuraDenominacion()}\"," : $json;
            $json = !empty($this->getFormatos()) ?  $json . "\"formatos\":\"{$this->getFormatos()}\"," : $json;
            $json = $json . "\"usuario\":\"{$this->getUsuario()}\",";
            $json = $json . "\"sesion\":\"{$this->getSesion()}\",";
            $json = $json . "\"estado\":\"{$this->getEstado()}\",";
            $json = $json . "\"estadoAlta\":\"{$this->getEstadoAlta()}\"}";
            return  $json;
    }
	
    public function toJsonWorkflow() : string {
 
        return "{
            \"descripcion\":\"{$this->getDescripcion()}\",
            \"usuario\":\"{$this->getUsuario()}\",
            \"sesion\":\"{$this->getSesion()}\",
            \"estado\":\"{$this->getEstado()}\"
          }";
    }

    public function getFromArray($array) : self {
 

        $origen = new OrigenDatos();
        $res = new self();
        $res->id = $array['id'];
        $res->denominacion = $array['denominacion'];
        $res->identificacion = $array['identificacion'];
        $res->descripcion = $array['descripcion'];
        $res->frecuenciaActulizacion = $array['frecuenciaActulizacion'];
        if ($array['fechaInicio']!= null) {
            $res->fechaInicio = new \DateTime($array['fechaInicio']);
        }
        if ($array['fechaFin']!= null) {
            $res->fechaFin = new \DateTime($array['fechaFin']);
        }
        $res->territorio = $array['territorio'];
        $res->instancias =  $array['instancias'];
        $res->organoResponsable =  $array['organoResponsable'];
        $res->finalidad =  $array['finalidad'];
        $res->condiciones =  $array['condiciones'];
        $res->vocabularios =  $array['vocabularios'];
        $res->formatos = $array['formatos'];
        $res->servicios =  $array['servicios'];
        $res->etiquetas =  $array['etiquetas'];
        $res->estructura =  $array['estructura'];
        $res->estructuraDenominacion =  $array['estructuraDenominacion'];
        $res->licencias =  $array['licencias'];
        $res->usuario =  $array['usuario'];
        $res->sesion =  $array['sesion'];
        $res->estado = $array['estado'];
        $res->estadoAlta = $array['estadoAlta'];
        $res->creadoEl = new \DateTime($array['creadoEl']);
        $res->actualizadoEn = new \DateTime($array['actualizadoEn']);
        $res->origenDatos =  $origen->getFromArray($array['origenDatos']);
        return $res;
    }

    public function getToView(ToolController $ToolController) : array {
 
        [$estadoKey, $estadoDescripcion] = $ToolController->DameEstadoDatos($this->getEstado());
        $identificador = $this->getIdentificacion();
        $denominacion = $this->getDenominacion();
        $descripcion = $this->getDescripcion();
        $frecuencia = !empty($this->getFrecuenciaActulizacion()) ? $this->getFrecuenciaActulizacion() : "";
        $inicio =  ($this->getFechaInicio()!=null) ? $this->getFechaInicio()->format('Y-m-d') : "";
        $fin =  ($this->getFechaFin()!=null)  ? $this->getFechaFin()->format('Y-m-d') : ""; 
        $Instancias = !empty($this->getInstancias()) ? explode(",",$this->getInstancias()) : array();
        $organo =  !empty($this->getOrganoResponsable()) ?  $this->getOrganoResponsable() : "";
        $condiciones =  !empty($this->getCondiciones())  ? $this->getCondiciones() : "";
        $finalidad =  !empty($this->getFinalidad())  ? $this->getFinalidad() : "";;
        $licencias =  !empty($this->getLicencias()) ? $this->getLicencias() : ""; ;
        $vocabularios = !empty($this->getVocabularios()) ? explode(",",$this->getVocabularios()) : array();
        $servicios = !empty($this->getServicios()) ? explode(",",$this->getServicios()) : array();
        $etiquetas = !empty($this->getEtiquetas()) ? explode(",",$this->getEtiquetas()) : array();
        $estructura =  !empty($this->getEstructura()) ?  $this->getEstructura() : "";
        $estructuraDenominacion =  !empty($this->getEstructuraDenominacion()) ?  $this->getEstructuraDenominacion():  "";
        $formatos =  !empty($this->getFormatos())  ? $this->getFormatos(): "";


        $datos  = array("estado"=>$estadoDescripcion,
                        "estadoKey" =>  $estadoKey,
                        "identificador"=> $identificador,
                        "denominacion" =>  $denominacion, 
                        "descripcion" => $descripcion,
                        "frecuencia" => $frecuencia,
                        "fechaInicio" =>$inicio,
                        "fechaFin" =>$fin,
                        "instancias" => $Instancias,
                        "organo" => $organo,
                        "condiciones"=> $condiciones,
                        "finalidad" => $finalidad,
                        "licencias" =>  $licencias,
                        "vocabularios" =>  $vocabularios,
                        "servicios" =>   $servicios,
                        "etiquetas" =>  $etiquetas,
                        "estructura" =>  $estructura,
                        "estructuraDenominacion" =>  $estructuraDenominacion,
                        "formatos" => $formatos);
        return $datos;
    }
 
}
