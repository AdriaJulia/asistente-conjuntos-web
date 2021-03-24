<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\BrowserKit\Cookie;

use App\Enum\EstadoDescripcionDatosEnum;
use App\Enum\EstadoAltaDatosEnum;
use App\Form\Model\DescripcionDatosDto;
use App\Form\Model\OrigenDatosDto;
use App\Entity\DescripcionDatos;
use App\Entity\OrigenDatos;
use App\Entity\OrigenDatosDatos;
use App\Enum\ModoFormularioOrigenEnum;
use App\Enum\TipoBaseDatosEnum;
use App\Enum\TipoOrigenDatosEnum;

use Ramsey\Uuid\Uuid;


class OrigenDatosManagerTest extends WebTestCase
{
    private $client = null;
    private $descripcionDatosManager = null;
    private $origenDatosManager = null;

    public function setUp(): void
    { 
        $this->client = static::createClient();
        $this->descripcionDatosManager = self::$container->get('App\Service\Manager\DescripcionDatosManager');
        $this->origenDatosManager = self::$container->get('App\Service\Manager\OrigenDatosManager');
        parent::setUp();
    }

    public function testPaso2UrlxmlAction()
    {      
        $this->logIn();
        $session = self::$container->get('session');
        $descripcionDatos = new DescripcionDatos();
        $descripcionDatosDto = DescripcionDatosDto::createFromDescripcionDatos($descripcionDatos);

         //paso1,1
        $descripcionDatosDto->denominacion = "Denominación conjunto datos";
        $descripcionDatosDto->descripcion = "Descripcion conjunto datos";
        $descripcionDatosDto->territorio = "CM:La Ribagorza";
        $descripcionDatosDto->frecuenciaActulizacion = "Semestral";
        $descripcionDatosDto->fechaInicio = "2021-01-01";
        $descripcionDatosDto->fechaFin = "2021-01-31";
        $descripcionDatosDto->instancias = "Intacia1,intancia2,Intancia3";
        $descripcionDatosDto->organoResponsable = "Organo responsable";
        $descripcionDatosDto->finalidad = "finalidad conjunto datos";
        $descripcionDatosDto->condiciones = "condiciones conjunto datos";
        $descripcionDatosDto->licencias = "licencias conjunto datos";
        $descripcionDatosDto->vocabularios = "vocabulario1, vocabulario2";
        $descripcionDatosDto->servicios = "servicios1, servicio2";

        $uuidGenerator = Uuid::uuid4();
   
        $descripcionDatos->setDenominacion($descripcionDatosDto->denominacion);
        $descripcionDatos->setIdentificacion($uuidGenerator->toString());
        $descripcionDatos->setDescripcion($descripcionDatosDto->descripcion);
        $descripcionDatos->setTerritorio($descripcionDatosDto->territorio);
        $descripcionDatos->setFrecuenciaActulizacion($descripcionDatosDto->frecuenciaActulizacion);    
        $descripcionDatos->setFechaInicio(new \DateTime($descripcionDatosDto->fechaInicio));
        $descripcionDatos->setFechaFin(new \DateTime($descripcionDatosDto->fechaFin));
        $descripcionDatos->setInstancias($descripcionDatosDto->instancias);

        $username = $session->getName();
        $descripcionDatos->setUsuario($username);
        $descripcionDatos->setSesion($session->getId());
        $descripcionDatos->setEstado(EstadoDescripcionDatosEnum::BORRADOR);
        $descripcionDatos->setEstadoAlta(EstadoAltaDatosEnum::paso3);
        $descripcionDatos = $this->descripcionDatosManager->create($descripcionDatos, $session);  
        $descripcionDatos->updatedTimestamps();

        $id= $descripcionDatos->getId();
        $idexiste = !empty($id);

        $this->assertTrue($idexiste);
        $this->assertEquals($uuidGenerator, $descripcionDatos->getIdentificacion());

        $contains = "html:contains('Nombre del conjunto de Datos')";
        $crawler = $this->client->request('GET', "/asistentecamposdatos/$id");
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter($contains)->count());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode()); 

        $origenDatos = new OrigenDatos();
        $descripcionDatosDto = OrigenDatosDto::createFromOrigenDatos($origenDatos);
        
        $descripcionDatosDto->idDescripcion = $id;
        $descripcionDatosDto->tipoOrigen = TipoOrigenDatosEnum::URL;
        $descripcionDatosDto->url = "http://localhost:8080/storage/default/Libro1.xml";
        $descripcionDatosDto->data = "";
        $descripcionDatosDto->tipoBaseDatos = "";
        $descripcionDatosDto->host = "";
        $descripcionDatosDto->puerto = "";
        $descripcionDatosDto->servicio = "";
        $descripcionDatosDto->esquema = "";
        $descripcionDatosDto->tabla = "";
        $descripcionDatosDto->usuarioDB = "";
        $descripcionDatosDto->contrasenaDB = "";
        $descripcionDatosDto->campos = "";
        
        $origenDatos->setIdDescripcion($descripcionDatosDto->idDescripcion);
        $origenDatos->setTipoOrigen($descripcionDatosDto->tipoOrigen);
        $origenDatos->setData($descripcionDatosDto->url);
        $origenDatos->setUsuario($username);
        $origenDatos->setSesion($session->getId());
        $origenDatos->updatedTimestamps();
        $origenDatos->setCampos("");
        [$origenDatos,$errorProceso] = $this->origenDatosManager->PruebaData($origenDatos,$session); 
        $this->assertTrue(empty($errorProceso));
        $this->assertTrue(!empty($origenDatos->getCampos()));

        $this->descripcionDatosManager->delete($id, $session); 
    }

    public function testPaso2UrljsonAction()
    {      
        $this->logIn();
        $session = self::$container->get('session');
        $descripcionDatos = new DescripcionDatos();
        $descripcionDatosDto = DescripcionDatosDto::createFromDescripcionDatos($descripcionDatos);

         //paso1,1
        $descripcionDatosDto->denominacion = "Denominación conjunto datos";
        $descripcionDatosDto->descripcion = "Descripcion conjunto datos";
        $descripcionDatosDto->territorio = "CM:La Ribagorza";
        $descripcionDatosDto->frecuenciaActulizacion = "Semestral";
        $descripcionDatosDto->fechaInicio = "2021-01-01";
        $descripcionDatosDto->fechaFin = "2021-01-31";
        $descripcionDatosDto->instancias = "Intacia1,intancia2,Intancia3";
        $descripcionDatosDto->organoResponsable = "Organo responsable";
        $descripcionDatosDto->finalidad = "finalidad conjunto datos";
        $descripcionDatosDto->condiciones = "condiciones conjunto datos";
        $descripcionDatosDto->licencias = "licencias conjunto datos";
        $descripcionDatosDto->vocabularios = "vocabulario1, vocabulario2";
        $descripcionDatosDto->servicios = "servicios1, servicio2";

        $uuidGenerator = Uuid::uuid4();
   
        $descripcionDatos->setDenominacion($descripcionDatosDto->denominacion);
        $descripcionDatos->setIdentificacion($uuidGenerator->toString());
        $descripcionDatos->setDescripcion($descripcionDatosDto->descripcion);
        $descripcionDatos->setTerritorio($descripcionDatosDto->territorio);
        $descripcionDatos->setFrecuenciaActulizacion($descripcionDatosDto->frecuenciaActulizacion);    
        $descripcionDatos->setFechaInicio(new \DateTime($descripcionDatosDto->fechaInicio));
        $descripcionDatos->setFechaFin(new \DateTime($descripcionDatosDto->fechaFin));
        $descripcionDatos->setInstancias($descripcionDatosDto->instancias);

        $username = $session->getName();
        $descripcionDatos->setUsuario($username);
        $descripcionDatos->setSesion($session->getId());
        $descripcionDatos->setEstado(EstadoDescripcionDatosEnum::BORRADOR);
        $descripcionDatos->setEstadoAlta(EstadoAltaDatosEnum::paso3);
        $descripcionDatos = $this->descripcionDatosManager->create($descripcionDatos, $session);  
        $descripcionDatos->updatedTimestamps();

        $id= $descripcionDatos->getId();
        $idexiste = !empty($id);

        $this->assertTrue($idexiste);
        $this->assertEquals($uuidGenerator, $descripcionDatos->getIdentificacion());

        $contains = "html:contains('Nombre del conjunto de Datos')";
        $crawler = $this->client->request('GET', "/asistentecamposdatos/$id");
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter($contains)->count());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode()); 

        $origenDatos = new OrigenDatos();
        $descripcionDatosDto = OrigenDatosDto::createFromOrigenDatos($origenDatos);

        $descripcionDatosDto->idDescripcion = $id;
        $descripcionDatosDto->tipoOrigen = TipoOrigenDatosEnum::URL;
        $descripcionDatosDto->url = "http://localhost:8080/storage/default/Libro1.json";
        $descripcionDatosDto->data = "";
        $descripcionDatosDto->tipoBaseDatos = "";
        $descripcionDatosDto->host = "";
        $descripcionDatosDto->puerto = "";
        $descripcionDatosDto->servicio = "";
        $descripcionDatosDto->esquema = "";
        $descripcionDatosDto->tabla = "";
        $descripcionDatosDto->usuarioDB = "";
        $descripcionDatosDto->contrasenaDB = "";
        $descripcionDatosDto->campos = "";
        
        $origenDatos->setIdDescripcion($descripcionDatosDto->idDescripcion);
        $origenDatos->setTipoOrigen($descripcionDatosDto->tipoOrigen);
        $origenDatos->setData($descripcionDatosDto->url);
        $origenDatos->setUsuario($username);
        $origenDatos->setSesion($session->getId());
        $origenDatos->updatedTimestamps();
        $origenDatos->setCampos("");
        [$origenDatos,$errorProceso] = $this->origenDatosManager->PruebaData($origenDatos,$session); 
        $this->assertTrue(empty($errorProceso));
        $this->assertTrue(!empty($origenDatos->getCampos()));

        $this->descripcionDatosManager->delete($id, $session); 
    }

    public function testPaso2UrlCsvAction()
    {      
        $this->logIn();
        $session = self::$container->get('session');
        $descripcionDatos = new DescripcionDatos();
        $descripcionDatosDto = DescripcionDatosDto::createFromDescripcionDatos($descripcionDatos);

         //paso1,1
        $descripcionDatosDto->denominacion = "Denominación conjunto datos";
        $descripcionDatosDto->descripcion = "Descripcion conjunto datos";
        $descripcionDatosDto->territorio = "CM:La Ribagorza";
        $descripcionDatosDto->frecuenciaActulizacion = "Semestral";
        $descripcionDatosDto->fechaInicio = "2021-01-01";
        $descripcionDatosDto->fechaFin = "2021-01-31";
        $descripcionDatosDto->instancias = "Intacia1,intancia2,Intancia3";
        $descripcionDatosDto->organoResponsable = "Organo responsable";
        $descripcionDatosDto->finalidad = "finalidad conjunto datos";
        $descripcionDatosDto->condiciones = "condiciones conjunto datos";
        $descripcionDatosDto->licencias = "licencias conjunto datos";
        $descripcionDatosDto->vocabularios = "vocabulario1, vocabulario2";
        $descripcionDatosDto->servicios = "servicios1, servicio2";

        $uuidGenerator = Uuid::uuid4();
   
        $descripcionDatos->setDenominacion($descripcionDatosDto->denominacion);
        $descripcionDatos->setIdentificacion($uuidGenerator->toString());
        $descripcionDatos->setDescripcion($descripcionDatosDto->descripcion);
        $descripcionDatos->setTerritorio($descripcionDatosDto->territorio);
        $descripcionDatos->setFrecuenciaActulizacion($descripcionDatosDto->frecuenciaActulizacion);    
        $descripcionDatos->setFechaInicio(new \DateTime($descripcionDatosDto->fechaInicio));
        $descripcionDatos->setFechaFin(new \DateTime($descripcionDatosDto->fechaFin));
        $descripcionDatos->setInstancias($descripcionDatosDto->instancias);

        $username = $session->getName();
        $descripcionDatos->setUsuario($username);
        $descripcionDatos->setSesion($session->getId());
        $descripcionDatos->setEstado(EstadoDescripcionDatosEnum::BORRADOR);
        $descripcionDatos->setEstadoAlta(EstadoAltaDatosEnum::paso3);
        $descripcionDatos = $this->descripcionDatosManager->create($descripcionDatos, $session);  
        $descripcionDatos->updatedTimestamps();

        $id= $descripcionDatos->getId();
        $idexiste = !empty($id);

        $this->assertTrue($idexiste);
        $this->assertEquals($uuidGenerator, $descripcionDatos->getIdentificacion());

        $contains = "html:contains('Nombre del conjunto de Datos')";
        $crawler = $this->client->request('GET', "/asistentecamposdatos/$id");
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter($contains)->count());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode()); 

        $origenDatos = new OrigenDatos();
        $descripcionDatosDto = OrigenDatosDto::createFromOrigenDatos($origenDatos);

        $descripcionDatosDto->idDescripcion = $id;
        $descripcionDatosDto->tipoOrigen = TipoOrigenDatosEnum::URL;
        $descripcionDatosDto->url = "http://localhost:8080/storage/default/Libro1.csv";
        $descripcionDatosDto->data = "";
        $descripcionDatosDto->tipoBaseDatos = "";
        $descripcionDatosDto->host = "";
        $descripcionDatosDto->puerto = "";
        $descripcionDatosDto->servicio = "";
        $descripcionDatosDto->esquema = "";
        $descripcionDatosDto->tabla = "";
        $descripcionDatosDto->usuarioDB = "";
        $descripcionDatosDto->contrasenaDB = "";
        $descripcionDatosDto->campos = "";
        

        //paso2 url xls
        $origenDatos->setIdDescripcion($descripcionDatosDto->idDescripcion);
        $origenDatos->setTipoOrigen($descripcionDatosDto->tipoOrigen);
        $origenDatos->setData($descripcionDatosDto->url);
        $origenDatos->setUsuario($username);
        $origenDatos->setSesion($session->getId());
        $origenDatos->updatedTimestamps();
        $origenDatos->setCampos("");
        [$origenDatos,$errorProceso] = $this->origenDatosManager->PruebaData($origenDatos,$session); 
        $this->assertTrue(empty($errorProceso));
        $this->assertTrue(!empty($origenDatos->getCampos()));

        $this->descripcionDatosManager->delete($id, $session); 
    }

    public function testPaso2UrlXlsAction()
    {      
        $this->logIn();
        $session = self::$container->get('session');
        $descripcionDatos = new DescripcionDatos();
        $descripcionDatosDto = DescripcionDatosDto::createFromDescripcionDatos($descripcionDatos);

         //paso1,1
        $descripcionDatosDto->denominacion = "Denominación conjunto datos";
        $descripcionDatosDto->descripcion = "Descripcion conjunto datos";
        $descripcionDatosDto->territorio = "CM:La Ribagorza";
        $descripcionDatosDto->frecuenciaActulizacion = "Semestral";
        $descripcionDatosDto->fechaInicio = "2021-01-01";
        $descripcionDatosDto->fechaFin = "2021-01-31";
        $descripcionDatosDto->instancias = "Intacia1,intancia2,Intancia3";
        $descripcionDatosDto->organoResponsable = "Organo responsable";
        $descripcionDatosDto->finalidad = "finalidad conjunto datos";
        $descripcionDatosDto->condiciones = "condiciones conjunto datos";
        $descripcionDatosDto->licencias = "licencias conjunto datos";
        $descripcionDatosDto->vocabularios = "vocabulario1, vocabulario2";
        $descripcionDatosDto->servicios = "servicios1, servicio2";

        $uuidGenerator = Uuid::uuid4();
   
        $descripcionDatos->setDenominacion($descripcionDatosDto->denominacion);
        $descripcionDatos->setIdentificacion($uuidGenerator->toString());
        $descripcionDatos->setDescripcion($descripcionDatosDto->descripcion);
        $descripcionDatos->setTerritorio($descripcionDatosDto->territorio);
        $descripcionDatos->setFrecuenciaActulizacion($descripcionDatosDto->frecuenciaActulizacion);    
        $descripcionDatos->setFechaInicio(new \DateTime($descripcionDatosDto->fechaInicio));
        $descripcionDatos->setFechaFin(new \DateTime($descripcionDatosDto->fechaFin));
        $descripcionDatos->setInstancias($descripcionDatosDto->instancias);

        $username = $session->getName();
        $descripcionDatos->setUsuario($username);
        $descripcionDatos->setSesion($session->getId());
        $descripcionDatos->setEstado(EstadoDescripcionDatosEnum::BORRADOR);
        $descripcionDatos->setEstadoAlta(EstadoAltaDatosEnum::paso3);
        $descripcionDatos = $this->descripcionDatosManager->create($descripcionDatos, $session);  
        $descripcionDatos->updatedTimestamps();

        $id= $descripcionDatos->getId();
        $idexiste = !empty($id);

        $this->assertTrue($idexiste);
        $this->assertEquals($uuidGenerator, $descripcionDatos->getIdentificacion());

        $contains = "html:contains('Nombre del conjunto de Datos')";
        $crawler = $this->client->request('GET', "/asistentecamposdatos/$id");
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter($contains)->count());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode()); 

        $origenDatos = new OrigenDatos();
        $descripcionDatosDto = OrigenDatosDto::createFromOrigenDatos($origenDatos);

        $descripcionDatosDto->idDescripcion = $id;
        $descripcionDatosDto->tipoOrigen = TipoOrigenDatosEnum::URL;
        $descripcionDatosDto->url = "http://localhost:8080/storage/default/Libro1.xls";
        $descripcionDatosDto->data = "";
        $descripcionDatosDto->tipoBaseDatos = "";
        $descripcionDatosDto->host = "";
        $descripcionDatosDto->puerto = "";
        $descripcionDatosDto->servicio = "";
        $descripcionDatosDto->esquema = "";
        $descripcionDatosDto->tabla = "";
        $descripcionDatosDto->usuarioDB = "";
        $descripcionDatosDto->contrasenaDB = "";
        $descripcionDatosDto->campos = "";
        
        $origenDatos->setIdDescripcion($descripcionDatosDto->idDescripcion);
        $origenDatos->setTipoOrigen($descripcionDatosDto->tipoOrigen);
        $origenDatos->setData($descripcionDatosDto->url);
        $origenDatos->setUsuario($username);
        $origenDatos->setSesion($session->getId());
        $origenDatos->updatedTimestamps();
        $origenDatos->setCampos("");
        [$origenDatos,$errorProceso] = $this->origenDatosManager->PruebaData($origenDatos,$session); 
        $this->assertTrue(empty($errorProceso));
        $this->assertTrue(!empty($origenDatos->getCampos()));

        $this->descripcionDatosManager->delete($id, $session); 
    }

    public function testPaso2DBSqlServerAction()
    {      
        $this->logIn();
        $session = self::$container->get('session');
        $descripcionDatos = new DescripcionDatos();
        $descripcionDatosDto = DescripcionDatosDto::createFromDescripcionDatos($descripcionDatos);

         //paso1,1
        $descripcionDatosDto->denominacion = "Denominación conjunto datos";
        $descripcionDatosDto->descripcion = "Descripcion conjunto datos";
        $descripcionDatosDto->territorio = "CM:La Ribagorza";
        $descripcionDatosDto->frecuenciaActulizacion = "Semestral";
        $descripcionDatosDto->fechaInicio = "2021-01-01";
        $descripcionDatosDto->fechaFin = "2021-01-31";
        $descripcionDatosDto->instancias = "Intacia1,intancia2,Intancia3";
        $descripcionDatosDto->organoResponsable = "Organo responsable";
        $descripcionDatosDto->finalidad = "finalidad conjunto datos";
        $descripcionDatosDto->condiciones = "condiciones conjunto datos";
        $descripcionDatosDto->licencias = "licencias conjunto datos";
        $descripcionDatosDto->vocabularios = "vocabulario1, vocabulario2";
        $descripcionDatosDto->servicios = "servicios1, servicio2";

        $uuidGenerator = Uuid::uuid4();
   
        $descripcionDatos->setDenominacion($descripcionDatosDto->denominacion);
        $descripcionDatos->setIdentificacion($uuidGenerator->toString());
        $descripcionDatos->setDescripcion($descripcionDatosDto->descripcion);
        $descripcionDatos->setTerritorio($descripcionDatosDto->territorio);
        $descripcionDatos->setFrecuenciaActulizacion($descripcionDatosDto->frecuenciaActulizacion);    
        $descripcionDatos->setFechaInicio(new \DateTime($descripcionDatosDto->fechaInicio));
        $descripcionDatos->setFechaFin(new \DateTime($descripcionDatosDto->fechaFin));
        $descripcionDatos->setInstancias($descripcionDatosDto->instancias);

        $username = $session->getName();
        $descripcionDatos->setUsuario($username);
        $descripcionDatos->setSesion($session->getId());
        $descripcionDatos->setEstado(EstadoDescripcionDatosEnum::BORRADOR);
        $descripcionDatos->setEstadoAlta(EstadoAltaDatosEnum::paso3);
        $descripcionDatos = $this->descripcionDatosManager->create($descripcionDatos, $session);  
        $descripcionDatos->updatedTimestamps();

        $id= $descripcionDatos->getId();
        $idexiste = !empty($id);

        $this->assertTrue($idexiste);
        $this->assertEquals($uuidGenerator, $descripcionDatos->getIdentificacion());

        $contains = "html:contains('Nombre del conjunto de Datos')";
        $crawler = $this->client->request('GET', "/asistentecamposdatos/$id");
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter($contains)->count());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode()); 

        $origenDatos = new OrigenDatos();
        $descripcionDatosDto = OrigenDatosDto::createFromOrigenDatos($origenDatos);

        //paso2 base SQLSERVER
        $descripcionDatosDto->idDescripcion = $id;
        $descripcionDatosDto->tipoOrigen = TipoOrigenDatosEnum::BASEDATOS;
        $descripcionDatosDto->url = "";
        $descripcionDatosDto->data = "";
        $descripcionDatosDto->host = "localhost";
        $descripcionDatosDto->esquema = "dummydb";
        $descripcionDatosDto->puerto = "1433";
        $descripcionDatosDto->tabla = "inventory";
        $descripcionDatosDto->usuarioDB = "sa";
        $descripcionDatosDto->contrasenaDB = "123456Sa@";
        $descripcionDatosDto->tipoBaseDatos = TipoBaseDatosEnum::SQLSERVER;
 
        $origenDatos->setIdDescripcion($descripcionDatosDto->idDescripcion);
        $origenDatos->setTipoOrigen($descripcionDatosDto->tipoOrigen);
        $origenDatos->setTipoBaseDatos($descripcionDatosDto->tipoBaseDatos);
        $origenDatos->setData($descripcionDatosDto->data);
        $origenDatos->setUsuario($username);
        $origenDatos->setHost($descripcionDatosDto->host);
        $origenDatos->setServicio("_");
        $origenDatos->setPuerto($descripcionDatosDto->puerto);
        $origenDatos->setEsquema($descripcionDatosDto->esquema);
        $origenDatos->setTabla($descripcionDatosDto->tabla);
        $origenDatos->setUsuarioDB($descripcionDatosDto->usuarioDB);
        $origenDatos->setContrasenaDB($descripcionDatosDto->contrasenaDB);  
 
        $origenDatos->setSesion($session->getId());
        $origenDatos->updatedTimestamps();
        $origenDatos->setCampos("");
        [$origenDatos,$errorProceso] = $this->origenDatosManager->PruebaDataBasedatos($origenDatos,$session); 
        $this->assertTrue(empty($errorProceso));
        $this->assertTrue(!empty($origenDatos->getCampos()));

        $this->descripcionDatosManager->delete($id, $session); 
    }

    public function testPaso2DBMysqlAction()
    {      
        $this->logIn();
        $session = self::$container->get('session');
        $descripcionDatos = new DescripcionDatos();
        $descripcionDatosDto = DescripcionDatosDto::createFromDescripcionDatos($descripcionDatos);

         //paso1,1
        $descripcionDatosDto->denominacion = "Denominación conjunto datos";
        $descripcionDatosDto->descripcion = "Descripcion conjunto datos";
        $descripcionDatosDto->territorio = "CM:La Ribagorza";
        $descripcionDatosDto->frecuenciaActulizacion = "Semestral";
        $descripcionDatosDto->fechaInicio = "2021-01-01";
        $descripcionDatosDto->fechaFin = "2021-01-31";
        $descripcionDatosDto->instancias = "Intacia1,intancia2,Intancia3";
        $descripcionDatosDto->organoResponsable = "Organo responsable";
        $descripcionDatosDto->finalidad = "finalidad conjunto datos";
        $descripcionDatosDto->condiciones = "condiciones conjunto datos";
        $descripcionDatosDto->licencias = "licencias conjunto datos";
        $descripcionDatosDto->vocabularios = "vocabulario1, vocabulario2";
        $descripcionDatosDto->servicios = "servicios1, servicio2";

        $uuidGenerator = Uuid::uuid4();
   
        $descripcionDatos->setDenominacion($descripcionDatosDto->denominacion);
        $descripcionDatos->setIdentificacion($uuidGenerator->toString());
        $descripcionDatos->setDescripcion($descripcionDatosDto->descripcion);
        $descripcionDatos->setTerritorio($descripcionDatosDto->territorio);
        $descripcionDatos->setFrecuenciaActulizacion($descripcionDatosDto->frecuenciaActulizacion);    
        $descripcionDatos->setFechaInicio(new \DateTime($descripcionDatosDto->fechaInicio));
        $descripcionDatos->setFechaFin(new \DateTime($descripcionDatosDto->fechaFin));
        $descripcionDatos->setInstancias($descripcionDatosDto->instancias);

        $username = $session->getName();
        $descripcionDatos->setUsuario($username);
        $descripcionDatos->setSesion($session->getId());
        $descripcionDatos->setEstado(EstadoDescripcionDatosEnum::BORRADOR);
        $descripcionDatos->setEstadoAlta(EstadoAltaDatosEnum::paso3);
        $descripcionDatos = $this->descripcionDatosManager->create($descripcionDatos, $session);  
        $descripcionDatos->updatedTimestamps();

        $id= $descripcionDatos->getId();
        $idexiste = !empty($id);

        $this->assertTrue($idexiste);
        $this->assertEquals($uuidGenerator, $descripcionDatos->getIdentificacion());

        $contains = "html:contains('Nombre del conjunto de Datos')";
        $crawler = $this->client->request('GET', "/asistentecamposdatos/$id");
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter($contains)->count());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode()); 

        $origenDatos = new OrigenDatos();
        $descripcionDatosDto = OrigenDatosDto::createFromOrigenDatos($origenDatos);

        //paso2 base MYSQL
        $descripcionDatosDto->idDescripcion = $id;
        $descripcionDatosDto->tipoOrigen = TipoOrigenDatosEnum::BASEDATOS;
        $descripcionDatosDto->url = "";
        $descripcionDatosDto->data = "";
        $descripcionDatosDto->host = "localhost";
        $descripcionDatosDto->esquema = "open_data_aragon";
        $descripcionDatosDto->puerto = "3306";
        $descripcionDatosDto->tabla = "user";
        $descripcionDatosDto->usuarioDB = "root";
        $descripcionDatosDto->contrasenaDB = "adminDP25@";
        $descripcionDatosDto->tipoBaseDatos = TipoBaseDatosEnum::MYSQL;
 
        $origenDatos->setIdDescripcion($descripcionDatosDto->idDescripcion);
        $origenDatos->setTipoOrigen($descripcionDatosDto->tipoOrigen);
        $origenDatos->setData($descripcionDatosDto->data);
        $origenDatos->setUsuario($username);
        $origenDatos->setTipoBaseDatos($descripcionDatosDto->tipoBaseDatos);
        $origenDatos->setHost($descripcionDatosDto->host);
        $origenDatos->setPuerto($descripcionDatosDto->puerto);
        $origenDatos->setServicio("_");
        $origenDatos->setEsquema($descripcionDatosDto->esquema);
        $origenDatos->setTabla($descripcionDatosDto->tabla);
        $origenDatos->setUsuarioDB($descripcionDatosDto->usuarioDB);
        $origenDatos->setContrasenaDB($descripcionDatosDto->contrasenaDB);  
 
        $origenDatos->setSesion($session->getId());
        $origenDatos->updatedTimestamps();
        $origenDatos->setCampos("");
 
        [$origenDatos,$errorProceso] = $this->origenDatosManager->PruebaDataBasedatos($origenDatos,$session); 
        $this->assertTrue(empty($errorProceso));
        $this->assertTrue(!empty($origenDatos->getCampos()));

        $this->descripcionDatosManager->delete($id, $session); 
    }

    public function testPaso2DBPostgrestAction()
    {      
        $this->logIn();
        $session = self::$container->get('session');
        $descripcionDatos = new DescripcionDatos();
        $descripcionDatosDto = DescripcionDatosDto::createFromDescripcionDatos($descripcionDatos);

         //paso1,1
        $descripcionDatosDto->denominacion = "Denominación conjunto datos";
        $descripcionDatosDto->descripcion = "Descripcion conjunto datos";
        $descripcionDatosDto->territorio = "CM:La Ribagorza";
        $descripcionDatosDto->frecuenciaActulizacion = "Semestral";
        $descripcionDatosDto->fechaInicio = "2021-01-01";
        $descripcionDatosDto->fechaFin = "2021-01-31";
        $descripcionDatosDto->instancias = "Intacia1,intancia2,Intancia3";
        $descripcionDatosDto->organoResponsable = "Organo responsable";
        $descripcionDatosDto->finalidad = "finalidad conjunto datos";
        $descripcionDatosDto->condiciones = "condiciones conjunto datos";
        $descripcionDatosDto->licencias = "licencias conjunto datos";
        $descripcionDatosDto->vocabularios = "vocabulario1, vocabulario2";
        $descripcionDatosDto->servicios = "servicios1, servicio2";

        $uuidGenerator = Uuid::uuid4();
   
        $descripcionDatos->setDenominacion($descripcionDatosDto->denominacion);
        $descripcionDatos->setIdentificacion($uuidGenerator->toString());
        $descripcionDatos->setDescripcion($descripcionDatosDto->descripcion);
        $descripcionDatos->setTerritorio($descripcionDatosDto->territorio);
        $descripcionDatos->setFrecuenciaActulizacion($descripcionDatosDto->frecuenciaActulizacion);    
        $descripcionDatos->setFechaInicio(new \DateTime($descripcionDatosDto->fechaInicio));
        $descripcionDatos->setFechaFin(new \DateTime($descripcionDatosDto->fechaFin));
        $descripcionDatos->setInstancias($descripcionDatosDto->instancias);

        $username = $session->getName();
        $descripcionDatos->setUsuario($username);
        $descripcionDatos->setSesion($session->getId());
        $descripcionDatos->setEstado(EstadoDescripcionDatosEnum::BORRADOR);
        $descripcionDatos->setEstadoAlta(EstadoAltaDatosEnum::paso3);
        $descripcionDatos = $this->descripcionDatosManager->create($descripcionDatos, $session);  
        $descripcionDatos->updatedTimestamps();

        $id= $descripcionDatos->getId();
        $idexiste = !empty($id);

        $this->assertTrue($idexiste);
        $this->assertEquals($uuidGenerator, $descripcionDatos->getIdentificacion());

        $contains = "html:contains('Nombre del conjunto de Datos')";
        $crawler = $this->client->request('GET', "/asistentecamposdatos/$id");
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter($contains)->count());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode()); 

        $origenDatos = new OrigenDatos();
        $descripcionDatosDto = OrigenDatosDto::createFromOrigenDatos($origenDatos);

        //paso2 base POSTGRESQL
        $descripcionDatosDto->idDescripcion = $id;
        $descripcionDatosDto->tipoOrigen = TipoOrigenDatosEnum::BASEDATOS;
        $descripcionDatosDto->url = "";
        $descripcionDatosDto->data = "";
        $descripcionDatosDto->host = "localhost";
        $descripcionDatosDto->esquema = "alfonso";
        $descripcionDatosDto->puerto = "5432";
        $descripcionDatosDto->tabla = "public.persons";
        $descripcionDatosDto->usuarioDB = "alfonso";
        $descripcionDatosDto->contrasenaDB = "123456@SA";
        $descripcionDatosDto->tipoBaseDatos = TipoBaseDatosEnum::POSTGRESQL;

        $origenDatos->setIdDescripcion($descripcionDatosDto->idDescripcion);
        $origenDatos->setTipoOrigen($descripcionDatosDto->tipoOrigen);
        $origenDatos->setData($descripcionDatosDto->data);
        $origenDatos->setUsuario($username);
        $origenDatos->setTipoBaseDatos($descripcionDatosDto->tipoBaseDatos);
        $origenDatos->setHost($descripcionDatosDto->host);
        $origenDatos->setPuerto($descripcionDatosDto->puerto);
        $origenDatos->setServicio("_");
        $origenDatos->setEsquema($descripcionDatosDto->esquema);
        $origenDatos->setTabla($descripcionDatosDto->tabla);
        $origenDatos->setUsuarioDB($descripcionDatosDto->usuarioDB);
        $origenDatos->setContrasenaDB($descripcionDatosDto->contrasenaDB);  

        $origenDatos->setSesion($session->getId());
        $origenDatos->updatedTimestamps();
        $origenDatos->setCampos("");

        [$origenDatos,$errorProceso] = $this->origenDatosManager->PruebaDataBasedatos($origenDatos,$session); 
        $this->assertTrue(empty($errorProceso));
        $this->assertTrue(!empty($origenDatos->getCampos()));

        $this->descripcionDatosManager->delete($id, $session); 
    }
     
    private function logIn()
    {

        $session = self::$container->get('session');

        // somehow fetch the user (e.g. using the user repository)
        $user = "MOCKSESSID";

        $firewallName = 'secure_area';
        // if you don't define multiple connected firewalls, the context defaults to the firewall name
        // See https://symfony.com/doc/current/reference/configuration/security.html#firewall-context
        $firewallContext = 'secured_area';

        // you may need to use a different token class depending on your application.
        // for example, when using Guard authentication you must instantiate PostAuthenticationGuardToken
        $token = new UsernamePasswordToken($user, null, $firewallName, ['ROLE_USER']);
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie); 

        
    }
}