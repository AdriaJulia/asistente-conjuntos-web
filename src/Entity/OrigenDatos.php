<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use \DateTime;

/*
 * Descripción: Es la clase entidad de el origen de datos del conjunto de datos. 
 *              Esta anotada con Doctrine, pero no persite en ninguna BD
 *              WebSite envía todas las operaciones de persistencia via apitest 
 *              que es donde realmente se guardan los datos.
 *              la notacion ORM es debida los formularios validadores y serializadores
 *              
 */

/**
 * @ORM\Entity()
 */
class OrigenDatos
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $tipoOrigen;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $tipoBaseDatos; 

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $data;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $host;

    /**
     * @ORM\Column(type="string", length=6, nullable=true)
     */
    private $puerto;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $servicio;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $esquema;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $tabla;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $usuarioDB;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $contrasenaDB;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $usuario;

     /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $sesion;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
    */
    private $campos;


    /**
     *  @ORM\Column(type="string", length=512, nullable=true)
     */
    private $alineacionEntidad; 

    /**
     *  @ORM\Column(type="text", nullable=true)
     */
    private $alineacionRelaciones;

    /**
     * @ORM\OneToOne(targetEntity=DescripcionDatos::class, inversedBy="origenDatos", cascade={"persist"})
     */
    private $descripcionDatos;

    
    /**
     * @ORM\Column(type="datetime")
     */
    private $creadoEl;

    /**
     * @ORM\Column(type="datetime")
     */
    private $actualizadoEn;


    private $idDescripcion;

    public function getIdDescripcion(): ?int
    {
        return $this->idDescripcion;
    }

    public function setIdDescripcion(int $idDescripcion): self
    {
        $this->idDescripcion = $idDescripcion;
        return $this;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTipoOrigen(): ?string 
    {
        return $this->tipoOrigen;
    }

    public function setTipoOrigen(string $tipoOrigen): self
    {
        $this->tipoOrigen = $tipoOrigen;

        return $this;
    }

    public function getTipoBaseDatos(): ?string
    {
        return $this->tipoBaseDatos;
    }

    public function setTipoBaseDatos(string $tipoBaseDatos): self
    {
        $this->tipoBaseDatos = $tipoBaseDatos;

        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(?string $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(?string $host): self
    {
        $this->host = $host;

        return $this;
    }

    public function getPuerto(): ?string
    {
        return $this->puerto;
    }

    public function setPuerto(?string $puerto): self
    {
        $this->puerto = $puerto;

        return $this;
    }

    public function getServicio(): ?string
    {
        return $this->servicio;
    }

    public function setServicio(?string $servicio): self
    {
        $this->servicio = $servicio;

        return $this;
    }

    public function getEsquema(): ?string
    {
        return $this->esquema;
    }

    public function setEsquema(?string $esquema): self
    {
        $this->esquema = $esquema;

        return $this;
    }

    public function getTabla(): ?string
    {
        return $this->tabla;
    }

    public function setTabla(?string $tabla): self
    {
        $this->tabla = $tabla;

        return $this;
    }

    public function getUsuarioDB(): ?string
    {
        return $this->usuarioDB;
    }

    public function setUsuarioDB(?string $usuarioDB): self
    {
        $this->usuarioDB = $usuarioDB;

        return $this;
    }

    public function getContrasenaDB(): ?string
    {
        return $this->contrasenaDB;
    }

    public function setContrasenaDB(?string $contrasenaDB): self
    {
        $this->contrasenaDB = $contrasenaDB;

        return $this;
    }

    public function getAlineacionEntidad(): ?string
    {
        return $this->alineacionEntidad;
    }

    public function setAlineacionEntidad(?string $alineacionEntidad): self
    {
        $this->alineacionEntidad = $alineacionEntidad;

        return $this;
    }

    public function getAlineacionRelaciones(): ?string
    {
        return $this->alineacionRelaciones;
    }

    public function setAlineacionRelaciones(?string $alineacionRelaciones): self
    {
        $this->alineacionRelaciones = $alineacionRelaciones;

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
    

    public function getCampos(): ?string
    {
        return $this->campos;
    }

    public function setCampos(?string $campos): self
    {
        $this->campos = $campos;

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

    public function getDescripcionDatos(): ?DescripcionDatos
    {
        return $this->descripcionDatos;
    }

    public function setDescripcionDatos(?DescripcionDatos $descripcionDatos): self
    {
        $this->descripcionDatos = $descripcionDatos;

        return $this;
    }

     /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps()
    { 
        $dateTimeNow = new DateTime('now');
        $this->setActualizadoEn($dateTimeNow);
        if ($this->getCreadoEl() === null) {
            $this->setCreadoEl($dateTimeNow);
        }
    }

    public function toJsonData() : string {
        $idDescripcion = 0;
        if ($this->getDescripcionDatos() != null) {
            $idDescripcion = $this->getDescripcionDatos()->getId();
        } else if ($this->idDescripcion != null) {
            $idDescripcion = $this->idDescripcion ;
        }
      return "{  
                \"idDescripcion\":\"{$idDescripcion}\",
                \"tipoOrigen\":\"{$this->getTipoOrigen()}\",
                \"data\":\"{$this->getData()}\",
                \"sesion\":\"{$this->getSesion()}\",
                \"usuario\":\"{$this->getUsuario()}\"
             }";
    }

    
    public function toJsonAlineacion() : string {

        return "{
            \"alineacionEntidad\":\"{$this->getAlineacionEntidad()}\",
            \"alineacionRelaciones\":\"{$this->getAlineacionRelaciones()}\",
            \"sesion\":\"{$this->getSesion()}\",
            \"usuario\":\"{$this->getUsuario()}\"
        }";

    }

    public function toJsonDatabase() : string {
        
        $idDescripcion = 0;
        if ($this->getDescripcionDatos() != null) {
            $idDescripcion = $this->getDescripcionDatos()->getId();
        } else if ($this->idDescripcion != null) {
            $idDescripcion = $this->idDescripcion ;
        }

        return "{
            \"idDescripcion\":\"{$idDescripcion}\",
            \"tipoOrigen\":\"{$this->getTipoOrigen()}\",
            \"tipoBaseDatos\":\"{$this->getTipoBaseDatos()}\",
            \"host\":\"{$this->getHost()}\",
            \"puerto\":\"{$this->getPuerto()}\",
            \"servicio\":\"{$this->getServicio()}\",
            \"esquema\":\"{$this->getEsquema()}\",
            \"tabla\":\"{$this->getTabla()}\",
            \"usuarioDB\":\"{$this->getUsuarioDB()}\",
            \"contrasenaDB\":\"{$this->getContrasenaDB()}\",
            \"sesion\":\"{$this->getSesion()}\",
            \"usuario\":\"{$this->getUsuario()}\"
        }";

    }

    public function getFromArray($array) : self {
 
         
        $res = new self();
        if ($array !=null) {
            if (array_key_exists('idDescripcion',$array )) {
               $res->idDescripcion = $array['idDescripcion'];
            }
        
            $res->id = $array['id'];
            $res->data = $array['data'];
            $res->tipoOrigen = $array['tipoOrigen'];
            $res->tipoBaseDatos = $array['tipoBaseDatos'];
            $res->host = $array['host'];
            $res->puerto = $array['puerto'];
            $res->servicio = $array['servicio'];
            $res->esquema = $array['esquema'];
            $res->tabla = $array['tabla'];
            $res->usuarioDB = $array['usuarioDB'];
            $res->contrasenaDB = $array['contrasenaDB'];
            $res->alineacionEntidad = $array['alineacionEntidad'];
            $res->alineacionRelaciones = $array['alineacionRelaciones'];
            $res->usuario =  $array['usuario'];
            $res->sesion =  $array['sesion'];
            $res->campos =  $array['campos'];
            $res->creadoEl = new \DateTime($array['creadoEl']);
            $res->actualizadoEn = new \DateTime($array['actualizadoEn']);
            
        }
        return $res;
    }

        
}
