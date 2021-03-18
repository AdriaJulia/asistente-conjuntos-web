<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


use App\Enum\EstadoDescripcionDatosEnum;
use App\Enum\EstadoAltaDatosEnum;
use App\Form\Model\DescripcionDatosDto;
use App\Entity\DescripcionDatos;

use Ramsey\Uuid\Uuid;




class DescripcionDatosManagerTest extends WebTestCase
{

    private $client = null;
    private $descripcionDatosManager = null;

    public function setUp(): void
    { 
        $this->client = static::createClient();
        $this->descripcionDatosManager = self::$container->get('App\Service\Manager\DescripcionDatosManager');
        parent::setUp();
    }

    public function testindexAction()
    {   
        $this->logIn();
        $contains = "html:contains('Listado banco de datos')";
        $crawler = $this->client->request('GET', '/asistentecamposdatos',['pagina'=>0,'tamano'=>0]);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter($contains)->count());

    }


    public function testPaso11Action()
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
        $descripcionDatos->setEstadoAlta(EstadoAltaDatosEnum::paso2);
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
        $this->descripcionDatosManager->delete($id, $session); 
    }

    public function testPaso12Action()
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
        $descripcionDatos->setEstadoAlta(EstadoAltaDatosEnum::paso2);
        $descripcionDatos = $this->descripcionDatosManager->create($descripcionDatos, $session);  
        $descripcionDatos->updatedTimestamps();

        $id= $descripcionDatos->getId();
        $idexiste = !empty($id);

        //paso1,2
        $descripcionDatosDto->organoResponsable = "Organo responsable";
        $descripcionDatosDto->finalidad = "finalidad conjunto datos";
        $descripcionDatosDto->condiciones = "condiciones conjunto datos";
        $descripcionDatosDto->licencias = "licencias conjunto datos";
        $descripcionDatosDto->vocabularios = "vocabulario1, vocabulario2";
        $descripcionDatosDto->servicios = "servicios1, servicio2";

        $descripcionDatos->setOrganoResponsable($descripcionDatosDto->organoResponsable);
        $descripcionDatos->setFinalidad($descripcionDatosDto->finalidad);
        $descripcionDatos->setCondiciones($descripcionDatosDto->condiciones);
        $descripcionDatos->setLicencias($descripcionDatosDto->licencias);
        $descripcionDatos->setVocabularios($descripcionDatosDto->vocabularios);
        $descripcionDatos->setServicios($descripcionDatosDto->servicios);

        $descripcionDatos->setEstadoAlta(EstadoAltaDatosEnum::paso3);
        $descripcionDatos->updatedTimestamps();

        $descripcionDatos = $this->descripcionDatosManager->save($descripcionDatos, $session);  

        $id= $descripcionDatos->getId();
        $idexiste = !empty($id);

        $this->assertTrue($idexiste);
        $this->assertEquals($uuidGenerator, $descripcionDatos->getIdentificacion());

        $contains = "html:contains('Nombre del conjunto de Datos')";
        $crawler = $this->client->request('GET', "/asistentecamposdatos/$id");
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter($contains)->count());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode()); 
        $this->descripcionDatosManager->delete($id, $session); 
    }
 
    public function testPaso13Action()
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
        $descripcionDatos->setEstadoAlta(EstadoAltaDatosEnum::paso2);
        $descripcionDatos = $this->descripcionDatosManager->create($descripcionDatos, $session);  
        $descripcionDatos->updatedTimestamps();

        $id= $descripcionDatos->getId();
        $idexiste = !empty($id);

         //paso1,3
        $descripcionDatos->setEstructura($descripcionDatosDto->estructura);
        $descripcionDatos->setEstructuraDenominacion($descripcionDatosDto->estructuraDenominacion);
        $descripcionDatos->setLicencias($descripcionDatosDto->licencias);
        $descripcionDatos->setFormatos($descripcionDatosDto->formatos);
        $descripcionDatos->setEtiquetas($descripcionDatosDto->etiquetas);
         
        $descripcionDatos->setSesion($session->getId());
        $descripcionDatos->setEstadoAlta(EstadoAltaDatosEnum::origen_url);

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