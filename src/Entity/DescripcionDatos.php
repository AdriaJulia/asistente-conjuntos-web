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
     * @ORM\Column(type="string", length=512, nullable=false)
     */
    private $titulo;

    /**
     * @ORM\Column(type="string", length=512, nullable=false)
     */
    private $identificacion;

    /**
     * @ORM\Column(type="text", nullable=false)
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
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $coberturaGeografica;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $publicador;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $tematica;

     /**
     * @ORM\Column(type="string",length=255, nullable=true)
     */
    private $licencias;


    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    private $vocabularios;

    /**
     * @ORM\Column(type="text",  nullable=true)
     */
    private $descripcionVocabularios;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    private $servicios;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $descripcionServicios;

    /**
     * @ORM\Column(type="string", length=1024, nullable=false)
     */
    private $etiquetas;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $idiomas;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $nivelDetalle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $usuario;

    /**
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $estado;

    /**
     * @ORM\Column(type="integer", nullable=true))
     */
    private $estadoCkan;

    /**
     * @ORM\Column(type="integer", nullable=true))
     */
    private $distribucion;

    /**
     * @ORM\Column(type="string", length=50, nullable=true))
     */
    private $estadoAlta;


    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $gaodcoreResourceId;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     */
    private $procesaAdo;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $sesion;

    /**
     * @ORM\Column(name="creado_el", type="datetime", nullable=false))
     */
    private $creadoEl;

    /**
     * @ORM\Column(name="actualizado_en", type="datetime", nullable=false))
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

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(?string $titulo): self
    {
        $this->titulo = $titulo;

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

    public function getCoberturaGeografica(): ?string
    {
        return $this->coberturaGeografica;;
    }


    public function getCoberturaGeograficaFicha(): ?string
    {
        $coberturaGeografica = $this->coberturaGeografica;
        $coberturaGeografica = str_replace("CO:","Comunidad de: ",$coberturaGeografica);
        $coberturaGeografica = str_replace("CM:","Comarca de: ",$coberturaGeografica);
        $coberturaGeografica = str_replace("MU:","Municipio de: ",$coberturaGeografica);
        $coberturaGeografica = str_replace("PR:","Provincia de: ",$coberturaGeografica);
        $coberturaGeografica = str_replace("OT:","Otros territorios de: ",$coberturaGeografica);
        return $coberturaGeografica;
    }

    public function setCoberturaGeografica(?string $coberturaGeografica): self
    {
        $this->coberturaGeografica = $coberturaGeografica;

        return $this;
    }

    public function getPublicador(): ?string
    {
        return $this->publicador;
    }

    public function setPublicador(?string $publicador): self
    {
        $this->publicador = $publicador;

        return $this;
    }

    public function getTematica(): ?string
    {
        return $this->tematica;
    }

    public function setTematica(?string $tematica): self
    {
        $this->tematica = $tematica;

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

    public function getDescripcionVocabularios(): ?string
    {
        return $this->descripcionVocabularios;
    }

    public function setDescripcionVocabularios(?string $descripcionVocabularios): self
    {
        $this->descripcionVocabularios = $descripcionVocabularios;

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

    public function getDescripcionServicios(): ?string
    {
        return $this->descripcionServicios;
    }

    public function setDescripcionServicios(?string $descripcionServicios): self
    {
        $this->descripcionServicios = $descripcionServicios;

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

    public function getIdiomas(): ?string
    {
        return $this->idiomas;
    }

    public function setIdiomas(?string $idiomas): self
    {
        $this->idiomas = $idiomas;

        return $this;
    }


    public function getNivelDetalle(): ?string
    {
        return $this->nivelDetalle;
    }

    public function setNivelDetalle(?string $nivelDetalle): self
    {
        $this->nivelDetalle = $nivelDetalle;

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

 
    public function getDistribucion(): ?int
    {
        return $this->distribucion;
    }

    public function setDistribucion(?int $distribucion): self
    {
        $this->distribucion = $distribucion;

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

    public function getGaodcoreResourceId(): ?string
    {
        return $this->gaodcoreResourceId;
    }

    public function setGaodcoreResourceId(?string $gaodcoreResourceId): self
    {
        $this->gaodcoreResourceId = $gaodcoreResourceId;

        return $this;
    }

    public function getProcesaAdo(): ?string
    {
        return $this->procesaAdo;
    }

    public function setProcesaAdo(?string $procesaAdo): self
    {
        $this->procesaAdo = $procesaAdo;

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
        $descripcion= base64_encode($this->getDescripcion());
        $coberturaGeografica = addslashes($this->getCoberturaGeografica());
        $titulo = addslashes($this->getTitulo());
        $niveldetalle= base64_encode($this->getNivelDetalle());
        $idiomas = addslashes($this->getIdiomas());
        $json ="{";
        $json = !empty($this->getTitulo()) ?  $json . "\"titulo\":\"{$titulo}\"," : $json;
        $json = !empty($this->getIdentificacion()) ?  $json . "\"identificacion\":\"{$this->getIdentificacion()}\"," : $json;
        $json = !empty($this->getDescripcion()) ?  $json . "\"descripcion\":\"{$descripcion}\"," : $json;
        $json = !empty($this->getFechaInicio()) ?  $json . "\"fechaInicio\":\"{$fechaInicio}\"," : $json;
        $json = !empty($this->getFechaFin()) ?  $json . "\"fechaFin\":\"{$fechaFin}\"," : $json;
        $json = !empty($this->getFrecuenciaActulizacion()) ?  $json . "\"frecuenciaActulizacion\":\"{$this->getFrecuenciaActulizacion()}\"," : $json;
        $json = !empty($this->getCoberturaGeografica()) ?  $json . "\"coberturaGeografica\":\"{$coberturaGeografica}\"," : $json;
        $json = !empty($this->getTematica()) ?  $json . "\"tematica\":\"{$this->getTematica()}\"," : $json;
        $json = !empty($this->getEtiquetas()) ?  $json . "\"etiquetas\":\"{$this->getEtiquetas()}\"," : $json;
        $json = !empty($this->getIdiomas()) ?  $json . "\"idiomas\":\"{$idiomas}\"," : $json;
        $json = !empty($this->getNivelDetalle()) ?  $json . "\"nivelDetalle\":\"{$niveldetalle}\"," : $json;
        $json = $json . "\"distribucion\":\"{$this->getDistribucion()}\",";
        $json = $json . "\"usuario\":\"{$this->getUsuario()}\",";
        $json = $json . "\"sesion\":\"{$this->getSesion()}\",";
        $json = $json . "\"estado\":\"{$this->getEstado()}\",";
        $json = $json . "\"estadoAlta\":\"{$this->getEstadoAlta()}\"}";
            
          return  $json;
    }

    public function toJsonUpdate() : string {
 
        $fechaInicio = !empty($this->getFechaInicio()) ? date_format($this->getFechaInicio(),'Y-m-d H:i'):  null;
        $fechaFin =  !empty($this->getFechaFin()) ?  date_format($this->getFechaFin(),'Y-m-d H:i') : null;
        $descripcion= base64_encode($this->getDescripcion());
        $descripcionVocabularios= base64_encode($this->getDescripcionVocabularios());
        $descripcionServicios= base64_encode($this->getDescripcionServicios());
        $niveldetalle= base64_encode($this->getNivelDetalle());
        
        $titulo = addslashes($this->getTitulo());
        $idiomas = addslashes($this->getIdiomas());

        $json ="{";
            $json = !empty($this->getTitulo()) ?  $json . "\"titulo\":\"{$titulo}\"," : $json;
            $json = !empty($this->getIdentificacion()) ?  $json . "\"identificacion\":\"{$this->getIdentificacion()}\"," : $json;
            $json = !empty($this->getDescripcion()) ?  $json . "\"descripcion\":\"{$descripcion}\"," : $json;
            $json = !empty($this->getFechaInicio()) ?  $json . "\"fechaInicio\":\"{$fechaInicio}\"," : $json;
            $json = !empty($this->getFechaFin()) ?  $json . "\"fechaFin\":\"{$fechaFin}\"," : $json;
            $json = !empty($this->getFrecuenciaActulizacion()) ?  $json . "\"frecuenciaActulizacion\":\"{$this->getFrecuenciaActulizacion()}\"," : $json;
            $json = !empty($this->getCoberturaGeografica()) ?  $json . "\"coberturaGeografica\":\"{$this->getCoberturaGeografica()}\"," : $json;
            $json = !empty($this->getPublicador()) ?  $json . "\"publicador\":\"{$this->getPublicador()}\"," : $json;
            $json = !empty($this->getTematica()) ?  $json . "\"tematica\":\"{$this->getTematica()}\"," : $json;
            $json = !empty($this->getLicencias()) ?  $json . "\"licencias\":\"{$this->getLicencias()}\"," : $json;
            $json = !empty($this->getVocabularios()) ?  $json . "\"vocabularios\":\"{$this->getVocabularios()}\"," : $json;
            $json = !empty($this->getDescripcionVocabularios()) ?  $json . "\"descripcionVocabularios\":\"{$descripcionVocabularios}\"," : $json;
            $json = !empty($this->getServicios()) ?  $json . "\"servicios\":\"{$this->getServicios()}\"," : $json;
            $json = !empty($this->getDescripcionServicios()) ?  $json . "\"descripcionServicios\":\"{$descripcionServicios}\"," : $json;
            $json = !empty($this->getEtiquetas()) ?  $json . "\"etiquetas\":\"{$this->getEtiquetas()}\"," : $json;
            $json = !empty($this->getIdiomas()) ?  $json . "\"idiomas\":\"{$idiomas}\"," : $json;
            $json = !empty($this->getNivelDetalle()) ?  $json . "\"nivelDetalle\":\"{$niveldetalle}\"," : $json;
            $json = $json . "\"distribucion\":\"{$this->getDistribucion()}\",";
            $json = $json . "\"usuario\":\"{$this->getUsuario()}\",";
            $json = $json . "\"sesion\":\"{$this->getSesion()}\",";
            $json = $json . "\"estado\":\"{$this->getEstado()}\",";
            $json = $json . "\"estadoAlta\":\"{$this->getEstadoAlta()}\"}";
            
            return  $json;
    }
	
    public function toJsonWorkflow() : string {
        $gaodcore = !empty($this->getGaodcoreResourceId()) ? $this->getGaodcoreResourceId() : "";
        $procesaAdo = !empty($this->getProcesaAdo()) ? $this->getProcesaAdo() : "0";
        $descripcion= base64_encode($this->getDescripcion());
        return "{
            \"descripcion\":\"{$descripcion}\",
            \"usuario\":\"{$this->getUsuario()}\",
            \"sesion\":\"{$this->getSesion()}\",
            \"estado\":\"{$this->getEstado()}\",
            \"procesaAdo\":\"{$procesaAdo}\",
            \"gaodCoreResourceId\":\"{$gaodcore}\"
          }";
    }

    public function getFromArray($array) : self {
 

        $origen = new OrigenDatos();
        $res = new self();
        $res->id = $array['id'];
        $res->titulo = $array['titulo'];
        $res->identificacion = $array['identificacion'];
        $res->descripcion = $array['descripcion'];
        $res->frecuenciaActulizacion = $array['frecuenciaActulizacion'];
        if ($array['fechaInicio']!= null) {
            $res->fechaInicio = new \DateTime($array['fechaInicio']);
        }
        if ($array['fechaFin']!= null) {
            $res->fechaFin = new \DateTime($array['fechaFin']);
        }
        $res->coberturaGeografica = $array['coberturaGeografica'];
        $res->publicador =  $array['publicador'];
        $res->tematica =  $array['tematica'];
        $res->vocabularios =  $array['vocabularios'];
        $res->descripcionVocabularios =  $array['descripcionVocabularios'];
        $res->servicios =  $array['servicios'];
        $res->descripcionServicios =  $array['descripcionServicios'];
        $res->etiquetas =  $array['etiquetas'];
        $res->idiomas =  $array['idiomas'];
        $res->nivelDetalle =  $array['nivelDetalle'];
        $res->licencias =  $array['licencias'];
        $res->distribucion =  $array['distribucion'];
        $res->usuario =  $array['usuario'];
        $res->sesion =  $array['sesion'];
        $res->estado = $array['estado'];
        $res->gaodcoreResourceId =  $array['gaodcoreResourceId'];
        $res->estadoAlta = $array['estadoAlta'];
        $res->creadoEl = new \DateTime($array['creadoEl']);
        $res->actualizadoEn = new \DateTime($array['actualizadoEn']);
        $res->origenDatos =  $origen->getFromArray($array['origenDatos']);
        return $res;
    }

    public function getToView(ToolController $ToolController) : array {
 
        [$estadoKey, $estadoDescripcion] = $ToolController->DameEstadoDatos($this->getEstado());
        $identificador = $this->getIdentificacion();
        $titulo = $this->getTitulo();
        $descripcion = $this->getDescripcion();
        $frecuencia = !empty($this->getFrecuenciaActulizacion()) ? $this->getFrecuenciaActulizacion() : "";
        $inicio =  ($this->getFechaInicio()!=null) ? $this->getFechaInicio()->format('Y-m-d') : "";
        $fin =  ($this->getFechaFin()!=null)  ? $this->getFechaFin()->format('Y-m-d') : ""; 
        $coberturaGeografica =  ($this->getCoberturaGeografica()!=null)  ? $this->getCoberturaGeograficaFicha() : "";      
        $publicador =  !empty($this->getPublicador()) ?  $this->getPublicador() : "";
        $tematica =  !empty($this->getTematica())  ? $this->getTematica() : "";
        $licencias =  !empty($this->getLicencias()) ? $this->getLicencias() : ""; 
        $vocabularios = !empty($this->getVocabularios()) ? explode(",",$this->getVocabularios()) : array();
        $descripcionVocabularios = !empty($this->getDescripcionVocabularios()) ? $this->getDescripcionVocabularios() : "";
        $servicios = !empty($this->getServicios()) ? explode(",",$this->getServicios()) : array();
        $descripcionServicios = !empty($this->getServicios()) ? $this->getDescripcionServicios() : "";
        $etiquetas = !empty($this->getEtiquetas()) ? explode(",",$this->getEtiquetas()) : array();
        $idiomas = !empty($this->getIdiomas()) ? explode(",",$this->getIdiomas()) : array();
        $nivelDetalle =  !empty($this->getNivelDetalle()) ?  $this->getNivelDetalle():  "";
        $distribucion =  !empty($this->getDistribucion()) ?  $this->getDistribucion():  "-1";


        $datos  = array("estado"=>$estadoDescripcion,
                        "estadoKey" =>  $estadoKey,
                        "identificador"=> $identificador,
                        "titulo" =>  $titulo, 
                        "descripcion" => $descripcion,
                        "frecuencia" => $frecuencia,
                        "fechaInicio" =>$inicio,
                        "fechaFin" =>$fin,
                        "coberturaGeografica" =>$coberturaGeografica,
                        "publicador" => $publicador,
                        "tematica" => $tematica,
                        "licencias" =>  $licencias,
                        "vocabularios" =>  $vocabularios,
                        "descripcionVocabularios" =>  $descripcionVocabularios,
                        "servicios" =>   $servicios,
                        "descripcionServicios" =>   $descripcionServicios,
                        "etiquetas" =>  $etiquetas,
                        "idiomas" => $idiomas,
                        "nivelDetalle" =>  $nivelDetalle,
                        "distribucion" => $distribucion);
        return $datos;
    }


}
